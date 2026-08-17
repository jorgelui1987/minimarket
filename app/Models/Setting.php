<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Setting extends Model
{
    protected $fillable = ['tenant_id', 'key', 'value'];

    public $timestamps = true;

    private static function tenantId(): ?int
    {
        return Auth::check() ? Auth::user()->tenant_id : null;
    }

    /**
     * Obtiene un valor de configuración de la empresa actual. Resiliente:
     * si no hay empresa (super admin / invitado) o la tabla no existe, devuelve el default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $tid = static::tenantId();
        if (! $tid) {
            return $default;
        }

        try {
            return Cache::rememberForever("setting.$tid.$key", function () use ($tid, $key, $default) {
                return static::where('tenant_id', $tid)->where('key', $key)->value('value') ?? $default;
            });
        } catch (Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $tid = static::tenantId();
        if (! $tid) {
            return;
        }

        static::updateOrCreate(
            ['tenant_id' => $tid, 'key' => $key],
            ['value' => $value]
        );
        Cache::forget("setting.$tid.$key");
    }

    public static function defaults(): array
    {
        return [
            'business_name' => 'Mi Minimarket',
            'ruc' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'currency' => 'S/',
            'igv_percent' => '18',
            'ticket_footer' => '¡Gracias por su compra!',
        ];
    }
}
