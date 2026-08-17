<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacturacionConfigController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirección raíz
Route::get('/', fn () => redirect()->route('dashboard'));

// Autenticación (invitados)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // ===== Plataforma (Super Admin) =====
    Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('empresas', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('empresas/{tenant}/editar', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('empresas/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::delete('empresas/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');

        Route::resource('planes', PlanController::class)->except('show')
            ->parameters(['planes' => 'plan'])->names('plans');

        // Perfil del super administrador
        Route::get('perfil', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('perfil', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::put('perfil/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
    });

    // ===== Aplicación del negocio (Tenant) =====
    Route::middleware('tenant')->group(function () {
        // Accesible aunque la suscripción esté vencida
        Route::get('suscripcion', [SubscriptionController::class, 'index'])->name('suscripcion');

        // Perfil del usuario (siempre accesible)
        Route::get('perfil', [ProfileController::class, 'edit'])->name('perfil.edit');
        Route::put('perfil', [ProfileController::class, 'update'])->name('perfil.update');
        Route::put('perfil/password', [ProfileController::class, 'updatePassword'])->name('perfil.password');

        Route::middleware('subscription')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Punto de Venta
            Route::get('pos', [PosController::class, 'index'])->name('pos');
            Route::post('pos', [PosController::class, 'store'])->name('pos.store');

            // Ventas
            Route::get('ventas', [SaleController::class, 'index'])->name('ventas.index');
            Route::get('ventas/{venta}', [SaleController::class, 'show'])->name('ventas.show');

            // Facturación electrónica (comprobante por venta)
            Route::post('ventas/{venta}/facturar', [BillingController::class, 'retry'])->name('ventas.facturar');
            Route::get('ventas/{venta}/xml', [BillingController::class, 'downloadXml'])->name('ventas.xml');
            Route::get('ventas/{venta}/cdr', [BillingController::class, 'downloadCdr'])->name('ventas.cdr');

            // Compras
            Route::get('compras', [PurchaseController::class, 'index'])->name('compras.index');
            Route::get('compras/crear', [PurchaseController::class, 'create'])->name('compras.create');
            Route::post('compras', [PurchaseController::class, 'store'])->name('compras.store');
            Route::get('compras/{compra}', [PurchaseController::class, 'show'])->name('compras.show');

            // Inventario (kardex y alertas)
            Route::get('inventario', [InventoryController::class, 'index'])->name('inventario.index');
            Route::get('inventario/kardex', [InventoryController::class, 'kardex'])->name('inventario.kardex');
            Route::post('inventario/ajuste', [InventoryController::class, 'adjust'])->name('inventario.adjust');

            // Devoluciones (notas de crédito)
            Route::get('devoluciones', [ReturnController::class, 'index'])->name('devoluciones.index');
            Route::get('devoluciones/crear', [ReturnController::class, 'create'])->name('devoluciones.create');
            Route::post('devoluciones', [ReturnController::class, 'store'])->name('devoluciones.store');
            Route::get('devoluciones/{devolucion}', [ReturnController::class, 'show'])->name('devoluciones.show');

            // Caja
            Route::get('caja', [CashRegisterController::class, 'index'])->name('caja.index');
            Route::post('caja/abrir', [CashRegisterController::class, 'open'])->name('caja.open');
            Route::post('caja/movimiento', [CashRegisterController::class, 'movement'])->name('caja.movement');
            Route::post('caja/cerrar', [CashRegisterController::class, 'close'])->name('caja.close');

            // Inventario
            Route::resource('productos', ProductController::class)->except('show')->parameters(['productos' => 'producto']);
            Route::resource('categorias', CategoryController::class)->except('show')->parameters(['categorias' => 'categoria']);
            Route::resource('promociones', PromotionController::class)->except('show')->parameters(['promociones' => 'promocion']);

            // Contactos
            Route::resource('clientes', CustomerController::class)->except('show')->parameters(['clientes' => 'cliente']);
            Route::resource('proveedores', SupplierController::class)->except('show')->parameters(['proveedores' => 'proveedore']);

            // Análisis
            Route::get('reportes', [ReportController::class, 'index'])->name('reportes');
            Route::get('reportes/export', [ReportController::class, 'export'])->name('reportes.export');
            Route::get('reportes/imprimir', [ReportController::class, 'print'])->name('reportes.print');

            // Sistema
            Route::resource('usuarios', UserController::class)->except('show')->parameters(['usuarios' => 'usuario']);
            Route::get('configuracion', [SettingController::class, 'edit'])->name('configuracion.edit');
            Route::put('configuracion', [SettingController::class, 'update'])->name('configuracion.update');

            // Configuración de Facturación Electrónica
            Route::get('facturacion/configuracion', [FacturacionConfigController::class, 'edit'])->name('facturacion.config.edit');
            Route::put('facturacion/configuracion', [FacturacionConfigController::class, 'update'])->name('facturacion.config.update');
            Route::post('facturacion/configuracion/probar', [FacturacionConfigController::class, 'test'])->name('facturacion.config.test');

            Route::get('backup', [BackupController::class, 'index'])->name('backup');
            Route::post('backup', [BackupController::class, 'store'])->name('backup.store');
            Route::get('backup/{file}/descargar', [BackupController::class, 'download'])->name('backup.download')->where('file', '[A-Za-z0-9_.\-]+');
            Route::post('backup/{file}/restaurar', [BackupController::class, 'restore'])->name('backup.restore')->where('file', '[A-Za-z0-9_.\-]+');
            Route::delete('backup/{file}', [BackupController::class, 'destroy'])->name('backup.destroy')->where('file', '[A-Za-z0-9_.\-]+');
        });
    });
});
