<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro por venta del comprobante electrónico y su estado ante la autoridad.
 * Es la persistencia ligera del ERP; el servicio de facturación mantiene su
 * propia base con el detalle completo (Fase 2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->index();
            $table->foreignId('sale_id')->nullable()->index();

            $table->string('country', 2)->default('PE');
            $table->string('document_type', 20);        // boleta | factura | nota_credito
            $table->string('series', 8);
            $table->string('number', 20);

            // pendiente | enviando | aceptado | observado | rechazado | anulado | error
            $table->string('status', 20)->default('pendiente')->index();
            $table->string('external_id')->nullable();  // CDR/nombre, CUFE, CAE, UUID...
            $table->string('authority_code')->nullable();
            $table->text('message')->nullable();
            $table->json('observations')->nullable();

            $table->string('xml_path')->nullable();
            $table->string('cdr_path')->nullable();
            $table->string('idempotency_key')->nullable()->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_documents');
    }
};
