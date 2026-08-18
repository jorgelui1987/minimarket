<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora de eventos de los DTEs (Chile/SII): folio reservado, XML firmado,
 * envío, consulta de estado, reintentos, etc. Complementa el registro ligero
 * de electronic_documents con trazabilidad de auditoría.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dte_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->index();
            $table->foreignId('electronic_document_id')->nullable()->index();
            $table->string('document_id')->nullable()->index();  // ej. "CL-76123456-7-T39-F123"
            $table->string('event', 50)->index();               // folio_reserved | signed | transmitted | status_checked | error
            $table->json('context')->nullable();
            $table->string('actor', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dte_logs');
    }
};