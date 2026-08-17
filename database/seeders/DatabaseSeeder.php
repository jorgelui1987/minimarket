<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Planes =====
        $planDefs = [
            ['name' => 'Prueba', 'slug' => 'prueba', 'price' => 0, 'max_products' => 50, 'max_users' => 2, 'max_monthly_sales' => 200],
            ['name' => 'Básico', 'slug' => 'basico', 'price' => 49.90, 'max_products' => 500, 'max_users' => 5, 'max_monthly_sales' => 2000],
            ['name' => 'Pro', 'slug' => 'pro', 'price' => 99.90, 'max_products' => -1, 'max_users' => -1, 'max_monthly_sales' => -1],
        ];
        foreach ($planDefs as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p + ['is_active' => true]);
        }
        $pro = Plan::where('slug', 'pro')->first();

        // ===== Super Admin de la plataforma (sin empresa) =====
        User::updateOrCreate(
            ['email' => 'superadmin@saas.test'],
            ['tenant_id' => null, 'name' => 'Super Administrador', 'password' => Hash::make('superadmin123'),
                'role' => 'admin', 'is_super_admin' => true, 'is_active' => true]
        );

        // ===== Empresa demo =====
        $tenant = Tenant::firstOrCreate(
            ['name' => 'Minimarket Demo'],
            ['plan_id' => $pro->id, 'status' => 'active', 'subscription_ends_at' => Carbon::now()->addYear(), 'is_active' => true]
        );

        // Admin de la empresa (antes de login para fijar tenant_id explícito)
        $admin = User::updateOrCreate(
            ['email' => 'admin@minimarket.test'],
            ['tenant_id' => $tenant->id, 'name' => 'Administrador del Sistema', 'password' => Hash::make('password'),
                'role' => 'admin', 'is_super_admin' => false, 'is_active' => true]
        );

        // A partir de aquí, autenticamos al admin para que el resto herede el tenant_id
        Auth::login($admin);

        User::updateOrCreate(
            ['email' => 'cajero@minimarket.test'],
            ['tenant_id' => $tenant->id, 'name' => 'Carlos Cajero', 'password' => Hash::make('password'),
                'role' => 'cajero', 'is_super_admin' => false, 'is_active' => true]
        );
        $cajeroId = User::where('email', 'cajero@minimarket.test')->value('id');

        // ===== Datos del negocio demo =====
        $categorias = ['Abarrotes', 'Lácteos', 'Bebidas', 'Limpieza', 'Cuidado Personal', 'Frutas y Verduras', 'Snacks', 'Panadería'];
        $catIds = [];
        foreach ($categorias as $c) {
            $catIds[$c] = Category::updateOrCreate(['name' => $c], ['is_active' => true])->id;
        }

        $proveedores = [
            ['name' => 'Distribuidora Andina S.A.C.', 'ruc' => '20123456789', 'phone' => '987654321'],
            ['name' => 'Alimentos del Sur E.I.R.L.', 'ruc' => '20567891234', 'phone' => '912345678'],
            ['name' => 'Bebidas Perú S.A.', 'ruc' => '20987654321', 'phone' => '998877665'],
        ];
        foreach ($proveedores as $p) {
            Supplier::updateOrCreate(['ruc' => $p['ruc']], $p + ['is_active' => true]);
        }
        $supplierIds = Supplier::pluck('id')->all();

        $productos = [
            ['Arroz Costeño 750g', 'Abarrotes', 3.50, 4.20], ['Azúcar Rubia 1kg', 'Abarrotes', 3.80, 4.50],
            ['Aceite Primor 1L', 'Abarrotes', 7.50, 9.90], ['Fideos Don Vittorio 500g', 'Abarrotes', 2.10, 2.90],
            ['Atún Florida', 'Abarrotes', 4.20, 5.50], ['Leche Gloria 400g', 'Lácteos', 3.10, 3.90],
            ['Yogurt Gloria 1L', 'Lácteos', 5.20, 6.90], ['Queso Fresco 250g', 'Lácteos', 6.50, 8.50],
            ['Mantequilla 200g', 'Lácteos', 4.80, 6.20], ['Coca Cola 1.5L', 'Bebidas', 4.50, 6.00],
            ['Inca Kola 1.5L', 'Bebidas', 4.50, 6.00], ['Agua San Luis 625ml', 'Bebidas', 1.00, 1.50],
            ['Cerveza Pilsen 355ml', 'Bebidas', 3.20, 4.50], ['Jugo Frugos 1L', 'Bebidas', 3.50, 4.80],
            ['Detergente Bolívar 780g', 'Limpieza', 5.50, 7.50], ['Lejía Clorox 1L', 'Limpieza', 3.00, 4.20],
            ['Jabón Bolívar', 'Limpieza', 1.80, 2.50], ['Papel Higiénico Suave x4', 'Limpieza', 4.50, 6.50],
            ['Shampoo Head & Shoulders', 'Cuidado Personal', 9.50, 12.90], ['Pasta Dental Colgate', 'Cuidado Personal', 3.50, 5.20],
            ['Jabón Dove', 'Cuidado Personal', 2.80, 4.00], ['Desodorante Rexona', 'Cuidado Personal', 6.50, 9.50],
            ['Plátano (kg)', 'Frutas y Verduras', 2.00, 3.50], ['Tomate (kg)', 'Frutas y Verduras', 2.50, 4.00],
            ['Papa Blanca (kg)', 'Frutas y Verduras', 1.80, 2.80], ['Manzana (kg)', 'Frutas y Verduras', 4.00, 6.50],
            ['Papas Lays 150g', 'Snacks', 3.50, 4.90], ['Galletas Oreo', 'Snacks', 1.80, 2.50],
            ['Chocolate Sublime', 'Snacks', 1.20, 1.80], ['Pan Francés (und)', 'Panadería', 0.20, 0.40],
            ['Pan de Molde Bimbo', 'Panadería', 4.50, 6.20],
        ];

        $productModels = [];
        foreach ($productos as $i => $pr) {
            $productModels[] = Product::updateOrCreate(
                ['name' => $pr[0]],
                [
                    'barcode' => '775' . str_pad((string) ($i + 1), 10, '0', STR_PAD_LEFT),
                    'category_id' => $catIds[$pr[1]],
                    'supplier_id' => $supplierIds[array_rand($supplierIds)],
                    'unit' => str_contains($pr[0], '(kg)') ? 'KG' : 'UND',
                    'cost' => $pr[2],
                    'price' => $pr[3],
                    'stock' => rand(0, 60),
                    'min_stock' => 10,
                    'is_active' => true,
                ]
            );
        }

        $nombres = ['María López', 'Juan Pérez', 'Ana Torres', 'Luis García', 'Rosa Flores', 'Pedro Ramos',
            'Carmen Díaz', 'José Castro', 'Lucía Vargas', 'Miguel Rojas', 'Cliente Varios'];
        foreach ($nombres as $n) {
            Customer::updateOrCreate(['name' => $n], [
                'doc_type' => 'DNI',
                'doc_number' => (string) rand(40000000, 79999999),
                'phone' => '9' . rand(10000000, 99999999),
                'birthday' => Carbon::now()->subYears(rand(20, 60))->subDays(rand(0, 364)),
                'is_active' => true,
            ]);
        }
        $customerIds = Customer::pluck('id')->all();

        if (Sale::count() === 0) {
            $methods = ['efectivo', 'efectivo', 'efectivo', 'tarjeta', 'yape', 'plin'];
            for ($d = 30; $d >= 0; $d--) {
                $date = Carbon::today()->subDays($d);
                $numVentas = $d === 0 ? rand(0, 2) : rand(3, 12);
                for ($v = 0; $v < $numVentas; $v++) {
                    $sale = Sale::create([
                        'document_type' => ['boleta', 'ticket', 'factura'][array_rand([0, 1, 2])],
                        'series' => 'B001',
                        'number' => str_pad((string) (Sale::count() + 1), 6, '0', STR_PAD_LEFT),
                        'customer_id' => $customerIds[array_rand($customerIds)],
                        'user_id' => $cajeroId,
                        'payment_method' => $methods[array_rand($methods)],
                        'status' => 'pagado',
                        'created_at' => $date->copy()->setTime(rand(8, 21), rand(0, 59)),
                        'updated_at' => $date,
                    ]);

                    $subtotal = 0;
                    $nItems = rand(1, 6);
                    foreach ((array) array_rand($productModels, $nItems) as $idx) {
                        $p = $productModels[$idx];
                        $qty = rand(1, 4);
                        $line = round($p->price * $qty, 2);
                        $subtotal += $line;
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $p->id,
                            'product_name' => $p->name,
                            'price' => $p->price,
                            'quantity' => $qty,
                            'subtotal' => $line,
                        ]);
                    }
                    $tax = round($subtotal - ($subtotal / 1.18), 2);
                    $sale->update(['subtotal' => round($subtotal - $tax, 2), 'tax' => $tax, 'total' => $subtotal]);
                }
            }
        }

        Auth::logout();
    }
}
