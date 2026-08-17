<?php

namespace Database\Seeders;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Genera 10 registros de demostración por cada módulo del sistema, con fechas
 * repartidas en los últimos días para que el Dashboard (KPIs, gráfico de línea
 * de "Últimos 7 días", barras por día de la semana y donas por categoría/forma
 * de pago) muestre información real.
 *
 * Es IDEMPOTENTE: al inicio elimina los datos demo generados en corridas
 * anteriores (identificados por marcadores) para no duplicar.
 *
 * Ejecutar:  php artisan db:seed --class=AddDataSeeder
 */
class AddDataSeeder extends Seeder
{
    /** Marcadores para identificar y limpiar datos demo previos. */
    private const CAT_MARK      = 'Categoría Extra %';
    private const SUP_NAME_MARK = 'Proveedor Extra %';
    private const CUS_MARK      = 'Cliente Frecuente %';
    private const PROD_MARK     = 'Producto Novedad %';
    private const PROMO_MARK    = 'Promo Especial %';
    private const SALE_SERIES   = 'B002';           // serie usada por ventas demo
    private const PURCHASE_MARK = 'FC-EX-%';        // documento de compras demo
    private const USER_MAIL     = '%@demo.minimarket.test';

    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            $this->command?->warn('No hay tenant. Corre primero: php artisan db:seed');
            return;
        }

        $admin = User::where('email', 'admin@minimarket.test')->first();
        if (! $admin) {
            $this->command?->warn('No existe admin@minimarket.test. Corre primero: php artisan db:seed');
            return;
        }

        // Autenticamos al admin para que el TenantScope asigne el tenant_id correcto.
        Auth::login($admin);

        $this->limpiarDemoPrevio();

        // Caja: reutilizamos una abierta o creamos una.
        $register = CashRegister::where('status', 'abierta')->first()
            ?? CashRegister::create([
                'user_id'        => $admin->id,
                'opening_amount' => 200,
                'status'         => 'abierta',
                'opened_at'      => Carbon::now()->subDays(10)->setTime(8, 0),
            ]);

        // Ofsets de día (0 = hoy) para las 10 ventas: cubren todos los días de la
        // última semana y repiten algunos, para que el gráfico de línea no sea plano.
        $ventaOffsets = [0, 1, 2, 3, 4, 5, 6, 1, 4, 6];
        $metodos      = ['efectivo', 'efectivo', 'tarjeta', 'yape', 'plin'];

        // Correlativos de venta arrancando después del máximo existente en la serie.
        $numVenta = 1000;

        for ($i = 1; $i <= 10; $i++) {
            // Fecha base del registro i (repartida en los últimos ~10 días).
            $date = Carbon::now()->subDays(10 - $i)->setTime(rand(9, 19), rand(0, 59));

            // 1) Categoría
            $cat = Category::create([
                'name'       => 'Categoría Extra ' . $i,
                'is_active'  => true,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // 2) Proveedor
            $sup = Supplier::create([
                'name'       => 'Proveedor Extra ' . $i . ' S.A.',
                'ruc'        => '2055500' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'phone'      => '9' . str_pad((string) (10000000 + $i), 9, '0', STR_PAD_LEFT),
                'is_active'  => true,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // 3) Cliente
            $cus = Customer::create([
                'name'       => 'Cliente Frecuente ' . $i,
                'doc_type'   => 'DNI',
                'doc_number' => '7100' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'phone'      => '9' . str_pad((string) (20000000 + $i), 9, '0', STR_PAD_LEFT),
                'birthday'   => Carbon::now()->subYears(20 + $i)->subDays($i * 7),
                'is_active'  => true,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // 4) Producto (stock inicial que luego ajustan compra y venta)
            $cost  = rand(10, 45) + 0.50;
            $price = round($cost * (1.35 + (rand(0, 30) / 100)), 2); // margen 35-65%
            $stock = rand(30, 120);
            $prod  = Product::create([
                'name'       => 'Producto Novedad ' . $i,
                'barcode'    => '8880000' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'category_id'=> $cat->id,
                'supplier_id'=> $sup->id,
                'unit'       => 'UND',
                'cost'       => $cost,
                'price'      => $price,
                'stock'      => $stock,
                'min_stock'  => 10,
                'is_active'  => true,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // 5) Promoción
            Promotion::create([
                'name'             => 'Promo Especial ' . $i,
                'product_id'       => $prod->id,
                'type'             => 'percentage',
                'discount_percent' => rand(5, 25),
                'starts_at'        => $date->copy()->startOfDay(),
                'ends_at'          => $date->copy()->addDays(15)->startOfDay(),
                'is_active'        => true,
                'created_at'       => $date,
                'updated_at'       => $date,
            ]);

            // 6) Usuario (rotando roles del sistema)
            $roles = ['cajero', 'almacen', 'cajero', 'admin', 'cajero'];
            User::create([
                'tenant_id'     => $tenant->id,
                'name'          => 'Colaborador Demo ' . $i,
                'email'         => 'colaborador' . $i . '@demo.minimarket.test',
                'password'      => Hash::make('password'),
                'role'          => $roles[($i - 1) % count($roles)],
                'is_super_admin'=> false,
                'phone'         => '9' . str_pad((string) (30000000 + $i), 9, '0', STR_PAD_LEFT),
                'is_active'     => true,
                'created_at'    => $date,
                'updated_at'    => $date,
            ]);

            // 7) Compra (suma stock) + item + movimiento de inventario 'entrada'
            $qtyCompra   = rand(20, 60);
            $totalCompra = round($cost * $qtyCompra, 2);
            $purchase = Purchase::create([
                'supplier_id'=> $sup->id,
                'user_id'    => $admin->id,
                'document'   => 'FC-EX-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'total'      => $totalCompra,
                'status'     => 'recibido',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            PurchaseItem::create([
                'purchase_id'=> $purchase->id,
                'product_id' => $prod->id,
                'quantity'   => $qtyCompra,
                'cost'       => $cost,
                'subtotal'   => $totalCompra,
            ]);
            $prod->increment('stock', $qtyCompra);
            StockMovement::create([
                'tenant_id'  => $tenant->id,
                'product_id' => $prod->id,
                'user_id'    => $admin->id,
                'type'       => 'entrada',
                'quantity'   => $qtyCompra,
                'stock_after'=> $prod->stock,
                'source'     => 'compra',
                'source_id'  => $purchase->id,
                'note'       => 'Compra ' . $purchase->document,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // 8) Venta (repartida en los últimos 7 días) + item + salida de stock
            $vDate = Carbon::now()->subDays($ventaOffsets[$i - 1])->setTime(rand(9, 21), rand(0, 59));
            $qtyVenta = rand(1, 4);
            $lineTotal = round($price * $qtyVenta, 2);      // precio con IGV incluido
            $tax = round($lineTotal - ($lineTotal / 1.18), 2);
            $net = round($lineTotal - $tax, 2);

            $sale = Sale::create([
                'document_type'   => 'boleta',
                'series'          => self::SALE_SERIES,
                'number'          => str_pad((string) ($numVenta + $i), 6, '0', STR_PAD_LEFT),
                'customer_id'     => $cus->id,
                'user_id'         => $admin->id,
                'cash_register_id'=> $register->id,
                'subtotal'        => $net,
                'tax'             => $tax,
                'total'           => $lineTotal,
                'payment_method'  => $metodos[($i - 1) % count($metodos)],
                'status'          => 'pagado',
                'created_at'      => $vDate,
                'updated_at'      => $vDate,
            ]);
            SaleItem::create([
                'sale_id'     => $sale->id,
                'product_id'  => $prod->id,
                'product_name'=> $prod->name,
                'price'       => $price,
                'quantity'    => $qtyVenta,
                'subtotal'    => $lineTotal,
            ]);
            $prod->decrement('stock', $qtyVenta);
            StockMovement::create([
                'tenant_id'  => $tenant->id,
                'product_id' => $prod->id,
                'user_id'    => $admin->id,
                'type'       => 'salida',
                'quantity'   => $qtyVenta,
                'stock_after'=> $prod->stock,
                'source'     => 'venta',
                'source_id'  => $sale->id,
                'note'       => 'Venta ' . $sale->series . '-' . $sale->number,
                'created_at' => $vDate,
                'updated_at' => $vDate,
            ]);

            // 9) Devolución (repone stock) + item + entrada de inventario
            $return = SaleReturn::create([
                'sale_id'         => $sale->id,
                'user_id'         => $admin->id,
                'cash_register_id'=> $register->id,
                'total'           => $lineTotal,
                'reason'          => $i % 2 === 0 ? 'Producto defectuoso' : 'Cambio de opinión',
                'created_at'      => $vDate->copy()->addHours(2),
                'updated_at'      => $vDate->copy()->addHours(2),
            ]);
            SaleReturnItem::create([
                'sale_return_id'=> $return->id,
                'product_id'    => $prod->id,
                'product_name'  => $prod->name,
                'price'         => $price,
                'quantity'      => $qtyVenta,
                'subtotal'      => $lineTotal,
            ]);

            // 10) Movimiento de caja (5 ingresos / 5 egresos)
            CashMovement::create([
                'cash_register_id'=> $register->id,
                'user_id'         => $admin->id,
                'type'            => $i % 2 === 0 ? 'ingreso' : 'egreso',
                'amount'          => rand(30, 180) + 0.50,
                'concept'         => ($i % 2 === 0 ? 'Ingreso extra ' : 'Gasto operativo ') . $i,
                'created_at'      => $date,
                'updated_at'      => $date,
            ]);
        }

        // -------------------------------------------------------------------
        // ENRIQUECIMIENTO DE VENTAS
        // Para que las gráficas del dashboard (línea "Últimos 7 días", barras
        // por día de la semana y donas por categoría/forma de pago) se vean
        // llenas y realistas, generamos varias ventas por día durante los
        // últimos 7 días. Los demás módulos permanecen en 10 registros; solo
        // crecen las ventas y sus movimientos de inventario asociados.
        // Siguen usando la serie B002, por lo que la limpieza idempotente de
        // limpiarDemoPrevio() también las elimina en la próxima corrida.
        // -------------------------------------------------------------------
        $demoProducts  = Product::where('barcode', 'like', '8880000%')->get();
        $demoCustomers = Customer::where('name', 'like', 'Cliente Frecuente %')->get();
        $metodosEnriq  = ['efectivo', 'efectivo', 'efectivo', 'tarjeta', 'tarjeta', 'yape', 'plin'];
        $numExtra      = 2000; // correlativo separado de las ventas base (1001-1010)
        $ventasExtra   = 0;

        if ($demoProducts->isNotEmpty()) {
            // Recorremos del día 6 (hace una semana) al día 0 (hoy).
            for ($d = 6; $d >= 0; $d--) {
                $ventasDia = rand(3, 5); // 3 a 5 ventas por día
                for ($k = 0; $k < $ventasDia; $k++) {
                    $vDate = Carbon::now()->subDays($d)->setTime(rand(8, 21), rand(0, 59));
                    if ($vDate->isFuture()) {
                        // Para "hoy" evitamos horas futuras (KPI "Ventas del día" real).
                        $vDate = Carbon::now()->subMinutes(rand(10, 180));
                    }

                    $prod      = $demoProducts->random();
                    $cus       = $demoCustomers->isNotEmpty() ? $demoCustomers->random() : null;
                    $qtyVenta  = rand(1, 3);
                    $lineTotal = round(((float) $prod->price) * $qtyVenta, 2); // precio con IGV incluido
                    $tax       = round($lineTotal - ($lineTotal / 1.18), 2);
                    $net       = round($lineTotal - $tax, 2);

                    $numExtra++;
                    $sale = Sale::create([
                        'document_type'   => 'boleta',
                        'series'          => self::SALE_SERIES,
                        'number'          => str_pad((string) $numExtra, 6, '0', STR_PAD_LEFT),
                        'customer_id'     => $cus?->id,
                        'user_id'         => $admin->id,
                        'cash_register_id'=> $register->id,
                        'subtotal'        => $net,
                        'tax'             => $tax,
                        'total'           => $lineTotal,
                        'payment_method'  => $metodosEnriq[array_rand($metodosEnriq)],
                        'status'          => 'pagado',
                        'created_at'      => $vDate,
                        'updated_at'      => $vDate,
                    ]);
                    SaleItem::create([
                        'sale_id'      => $sale->id,
                        'product_id'   => $prod->id,
                        'product_name' => $prod->name,
                        'price'        => $prod->price,
                        'quantity'     => $qtyVenta,
                        'subtotal'     => $lineTotal,
                    ]);
                    $prod->decrement('stock', $qtyVenta);
                    StockMovement::create([
                        'tenant_id'  => $tenant->id,
                        'product_id' => $prod->id,
                        'user_id'    => $admin->id,
                        'type'       => 'salida',
                        'quantity'   => $qtyVenta,
                        'stock_after'=> $prod->stock,
                        'source'     => 'venta',
                        'source_id'  => $sale->id,
                        'note'       => 'Venta ' . $sale->series . '-' . $sale->number,
                        'created_at' => $vDate,
                        'updated_at' => $vDate,
                    ]);
                    $ventasExtra++;
                }
            }
        }

        Auth::logout();

        $this->command?->info("AddDataSeeder: 10 registros por módulo + {$ventasExtra} ventas adicionales repartidas en los últimos 7 días.");
    }

    /**
     * Elimina los datos demo de corridas anteriores para mantener la idempotencia.
     * Se borra en orden de dependencias (hijos antes que padres).
     */
    private function limpiarDemoPrevio(): void
    {
        // Ventas demo (serie B002) y todo lo colgado de ellas.
        $saleIds = Sale::where('series', self::SALE_SERIES)->pluck('id');
        if ($saleIds->isNotEmpty()) {
            $returnIds = SaleReturn::whereIn('sale_id', $saleIds)->pluck('id');
            SaleReturnItem::whereIn('sale_return_id', $returnIds)->delete();
            SaleReturn::whereIn('id', $returnIds)->delete();
            SaleItem::whereIn('sale_id', $saleIds)->delete();
            Sale::whereIn('id', $saleIds)->delete();
        }

        // Compras demo.
        $purchaseIds = Purchase::where('document', 'like', self::PURCHASE_MARK)->pluck('id');
        if ($purchaseIds->isNotEmpty()) {
            PurchaseItem::whereIn('purchase_id', $purchaseIds)->delete();
            Purchase::whereIn('id', $purchaseIds)->delete();
        }

        // Productos demo y sus dependientes (promos, movimientos de stock).
        $prodIds = Product::where('barcode', 'like', '8880000%')->pluck('id');
        if ($prodIds->isNotEmpty()) {
            Promotion::whereIn('product_id', $prodIds)->delete();
            StockMovement::whereIn('product_id', $prodIds)->delete();
            Product::whereIn('id', $prodIds)->delete();
        }

        Promotion::where('name', 'like', self::PROMO_MARK)->delete();
        Category::where('name', 'like', self::CAT_MARK)->delete();
        Supplier::where('name', 'like', self::SUP_NAME_MARK)->delete();
        Customer::where('name', 'like', self::CUS_MARK)->delete();
        User::where('email', 'like', self::USER_MAIL)->delete();

        // Movimientos de caja demo (agrupado para no romper el filtro de tenant).
        CashMovement::where(function ($q) {
            $q->where('concept', 'like', 'Ingreso extra %')
              ->orWhere('concept', 'like', 'Gasto operativo %');
        })->delete();
    }
}
