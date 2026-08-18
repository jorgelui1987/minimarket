<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('photo_receipt')->nullable()->after('document');   // foto de la boleta/factura
            $table->string('photo_products')->nullable()->after('photo_receipt'); // foto de los productos
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['photo_receipt', 'photo_products']);
        });
    }
};