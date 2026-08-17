<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    private function path(): string
    {
        $path = storage_path('app/backups');
        if (! is_dir($path)) {
            mkdir($path, 0775, true);
        }

        return $path;
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403, 'Solo los administradores pueden gestionar backups.');
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);

        $files = [];
        foreach (glob($this->path() . '/*.sql') as $f) {
            $files[] = [
                'name' => basename($f),
                'size' => filesize($f),
                'date' => filemtime($f),
            ];
        }
        usort($files, fn ($a, $b) => $b['date'] <=> $a['date']);

        return view('backups.index', compact('files'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $file = $this->path() . '/backup_' . now()->format('Ymd_His') . '.sql';
        $pdo = DB::getPdo();

        $sql = "-- Backup Mi Minimarket\n-- Generado: " . now()->toDateTimeString() . "\n\n";
        $sql .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach (DB::select('SHOW TABLES') as $row) {
            $table = array_values((array) $row)[0];

            $createRow = (array) DB::select("SHOW CREATE TABLE `$table`")[0];
            $createSql = $createRow['Create Table'] ?? array_values($createRow)[1];

            $sql .= "DROP TABLE IF EXISTS `$table`;\n$createSql;\n\n";

            foreach (DB::table($table)->cursor() as $record) {
                $data = (array) $record;
                $cols = '`' . implode('`,`', array_keys($data)) . '`';
                $vals = implode(',', array_map(function ($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote((string) $v);
                }, array_values($data)));
                $sql .= "INSERT INTO `$table` ($cols) VALUES ($vals);\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($file, $sql);

        return back()->with('success', 'Backup generado: ' . basename($file));
    }

    public function download(Request $request, string $file): BinaryFileResponse
    {
        $this->authorizeAdmin($request);

        $full = $this->path() . '/' . basename($file);
        abort_unless(is_file($full), 404);

        return response()->download($full);
    }

    public function restore(Request $request, string $file): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $full = $this->path() . '/' . basename($file);
        if (! is_file($full)) {
            return back()->with('error', 'El archivo de backup no existe.');
        }

        try {
            DB::unprepared(file_get_contents($full));
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }

        return back()->with('success', 'Base de datos restaurada desde ' . basename($file));
    }

    public function destroy(Request $request, string $file): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $full = $this->path() . '/' . basename($file);
        if (is_file($full)) {
            unlink($full);
        }

        return back()->with('success', 'Backup eliminado.');
    }
}
