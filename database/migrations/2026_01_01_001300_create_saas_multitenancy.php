<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tablas que pertenecen a un tenant. */
    private array $tenantTables = [
        'categories', 'suppliers', 'products', 'customers',
        'sales', 'sale_items', 'purchases', 'purchase_items',
        'promotions', 'cash_registers', 'cash_movements', 'settings',
    ];

    public function up(): void
    {
        // 1) Planes
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('max_products')->default(-1);   // -1 = ilimitado
            $table->integer('max_users')->default(-1);
            $table->integer('max_monthly_sales')->default(-1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2) Empresas (tenants)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ruc', 11)->nullable();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('trial'); // trial, active, suspended
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3) Usuarios: tenant_id + is_super_admin
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_super_admin')->default(false)->after('role');
        });

        // 4) tenant_id en tablas del negocio
        foreach ($this->tenantTables as $t) {
            if (Schema::hasTable($t) && ! Schema::hasColumn($t, 'tenant_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
                });
            }
        }

        // settings: la clave ahora es única por empresa
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique(['key']);
                $table->unique(['tenant_id', 'key']);
            });
        }

        // 5) Datos por defecto + migración de datos existentes
        $now = Carbon::now();

        $plans = [
            ['name' => 'Prueba', 'slug' => 'prueba', 'price' => 0, 'max_products' => 50, 'max_users' => 2, 'max_monthly_sales' => 200],
            ['name' => 'Básico', 'slug' => 'basico', 'price' => 49.90, 'max_products' => 500, 'max_users' => 5, 'max_monthly_sales' => 2000],
            ['name' => 'Pro', 'slug' => 'pro', 'price' => 99.90, 'max_products' => -1, 'max_users' => -1, 'max_monthly_sales' => -1],
        ];
        foreach ($plans as $p) {
            DB::table('plans')->insert($p + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Super admin de la plataforma
        if (! DB::table('users')->where('is_super_admin', true)->exists()) {
            DB::table('users')->insert([
                'tenant_id' => null,
                'name' => 'Super Administrador',
                'email' => 'superadmin@saas.test',
                'password' => Hash::make('superadmin123'),
                'role' => 'admin',
                'is_super_admin' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Empresa demo "Minimarket Demo" (SIEMPRE, para que el login funcione tras migrar)
        $proId = DB::table('plans')->where('slug', 'pro')->value('id');
        $tenantId = DB::table('tenants')->where('name', 'Minimarket Demo')->value('id');
        if (! $tenantId) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Minimarket Demo',
                'plan_id' => $proId,
                'status' => 'active',
                'subscription_ends_at' => $now->copy()->addYear(),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Usuario administrador demo (SIEMPRE) -> admin@minimarket.test / password
        if (! DB::table('users')->where('email', 'admin@minimarket.test')->exists()) {
            DB::table('users')->insert([
                'tenant_id' => $tenantId,
                'name' => 'Administrador del Sistema',
                'email' => 'admin@minimarket.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_super_admin' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Asignar cualquier dato previo sin empresa al tenant demo
        DB::table('users')->whereNull('tenant_id')->where('is_super_admin', false)->update(['tenant_id' => $tenantId]);
        foreach ($this->tenantTables as $t) {
            if (Schema::hasTable($t)) {
                DB::table($t)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'key']);
                $table->dropConstrainedForeignId('tenant_id');
                $table->unique('key');
            });
        }

        foreach ($this->tenantTables as $t) {
            if ($t === 'settings') {
                continue;
            }
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'tenant_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('tenant_id');
                });
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn('is_super_admin');
        });

        Schema::dropIfExists('tenants');
        Schema::dropIfExists('plans');
    }
};
