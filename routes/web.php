<?php

use App\Http\Controllers\AdvanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FilamentInventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalePaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShareholderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;




Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

//---------------------------------------
    Route::get('/media/{path}', function (string $path) {
        abort_if(str_contains($path, '..'), 404);

        $headers = ['Cache-Control' => 'private, max-age=3600'];

        // the public disk holds files meant to be shown (logos, avatars, ...)
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path, null, $headers);
        }

        // older avatars were saved on the private disk: allow only that folder
        if (str_starts_with($path, 'avatars/') && Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path, null, $headers);
        }

        abort(404);
    })->where('path', '.*')->name('media');


    // ------------------------------------------------------------------
    // Sales (create before the {sale} wildcard)
    // ------------------------------------------------------------------
    Route::middleware('can:sales.create')->group(function () {
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
    });
    Route::middleware('can:sales.view')->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    });
    Route::middleware('can:sales.edit')->group(function () {
        Route::get('sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
        Route::put('sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
        Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
        Route::post('sales/{sale}/payments', [SalePaymentController::class, 'store'])->name('sales.payments.store');
    });
    Route::middleware('can:sales.delete')->group(function () {
        Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    });

    // ------------------------------------------------------------------
    // Users & Settings
    // ------------------------------------------------------------------
    Route::middleware('can:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware('can:users.create')->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware('can:users.edit')->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });

        Route::middleware('can:users.delete')->group(function () {
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('can:settings.view')->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    });
    Route::middleware('can:settings.edit')->group(function () {
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/reports/send-now', [SettingController::class, 'sendReportNow'])->name('settings.reports.sendNow');
        Route::post('settings/backup/run', [SettingController::class, 'backupNow'])->name('settings.backup.run');
        Route::post('settings/backup/restore', [SettingController::class, 'restore'])->name('settings.backup.restore');
    });

    // About
    Route::get('/about', function () {
        return view('about.index');
    })->name('about');

    // Roles & permissions
    Route::middleware('can:roles.manage')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // Audit logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->middleware('can:audit.view')->name('audit-logs.index');

    // ------------------------------------------------------------------
    // Customers
    // NOTE: create must be registered before the {customer} wildcard route,
    // otherwise "customers/create" is matched by "customers/{customer}" and
    // Laravel tries (and fails) to model-bind a Customer with id "create",
    // resulting in a 404.
    // ------------------------------------------------------------------
    Route::middleware('can:customers.create')->group(function () {
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    });
    Route::middleware('can:customers.view')->group(function () {
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    });
    Route::middleware('can:customers.edit')->group(function () {
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    });
    Route::middleware('can:customers.delete')->group(function () {
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // ------------------------------------------------------------------
    // Suppliers (create before {supplier})
    // ------------------------------------------------------------------
    Route::middleware('can:suppliers.create')->group(function () {
        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware('can:suppliers.view')->group(function () {
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    });
    Route::middleware('can:suppliers.edit')->group(function () {
        Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    });
    Route::middleware('can:suppliers.delete')->group(function () {
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // ------------------------------------------------------------------
    // Expenses (create before {expense})
    // ------------------------------------------------------------------
    Route::middleware('can:expenses.create')->group(function () {
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    });
    Route::middleware('can:expenses.view')->group(function () {
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    });
    Route::middleware('can:expenses.edit')->group(function () {
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    });
    Route::middleware('can:expenses.delete')->group(function () {
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // ------------------------------------------------------------------
    // Filament inventory
    // ------------------------------------------------------------------
    Route::middleware('can:filament.view')->group(function () {
        Route::get('filament', [FilamentInventoryController::class, 'index'])->name('filament.index');
    });
    Route::middleware('can:filament.create')->group(function () {
        Route::get('filament/create', [FilamentInventoryController::class, 'create'])->name('filament.create');
        Route::post('filament', [FilamentInventoryController::class, 'store'])->name('filament.store');
    });
    Route::middleware('can:filament.edit')->group(function () {
        Route::get('filament/{filament}/edit', [FilamentInventoryController::class, 'edit'])->name('filament.edit');
        Route::put('filament/{filament}', [FilamentInventoryController::class, 'update'])->name('filament.update');
    });
    Route::middleware('can:filament.delete')->group(function () {
        Route::delete('filament/{filament}', [FilamentInventoryController::class, 'destroy'])->name('filament.destroy');
    });

    // ------------------------------------------------------------------
    // Placeholders: printers / orders / queue
    // ------------------------------------------------------------------
    Route::middleware('can:printers.view')->group(function () {
        Route::view('/printer', 'pages.coming-soon')->name('printers.index');
    });
    Route::middleware('can:orders.view')->group(function () {
        Route::view('/orders', 'pages.coming-soon')->name('orders.index');
    });
    Route::middleware('can:queue.view')->group(function () {
        Route::view('/queue', 'pages.coming-soon')->name('queue.index');
    });

    // ------------------------------------------------------------------
    // Supplies (create before {supply})
    // ------------------------------------------------------------------
    Route::middleware('can:supplies.create')->group(function () {
        Route::get('/supplies/create', [SupplyController::class, 'create'])->name('supplies.create');
        Route::post('/supplies', [SupplyController::class, 'store'])->name('supplies.store');
    });
    Route::middleware('can:supplies.view')->group(function () {
        Route::get('/supplies', [SupplyController::class, 'index'])->name('supplies.index');
        Route::get('/supplies/{supply}', [SupplyController::class, 'show'])->name('supplies.show');
    });
    Route::middleware('can:supplies.edit')->group(function () {
        Route::get('/supplies/{supply}/edit', [SupplyController::class, 'edit'])->name('supplies.edit');
        Route::put('/supplies/{supply}', [SupplyController::class, 'update'])->name('supplies.update');
        Route::post('supplies/purchases/{purchase}/apply', [SupplyController::class, 'applyPurchase'])->name('supplies.applyPurchase');
    });
    Route::middleware('can:supplies.delete')->group(function () {
        Route::delete('/supplies/{supply}', [SupplyController::class, 'destroy'])->name('supplies.destroy');
    });

Route::post('supplies/{supply}/mark-damaged', [SupplyController::class, 'markDamaged'])->name('supplies.mark-damaged');
Route::post('supplies/{supply}/restore-damaged', [SupplyController::class, 'restoreDamaged'])->name('supplies.restore-damaged');


    Route::post('purchases/{purchase}/pay', [PurchaseController::class, 'pay'])->name('purchases.pay');
Route::post('suppliers/{supplier}/pay', [SupplierController::class, 'pay'])->name('suppliers.pay');


    // ------------------------------------------------------------------
    // Products (create before {product})
    // ------------------------------------------------------------------
    Route::middleware('can:products.create')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
    });
    Route::middleware('can:products.view')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    });
    Route::middleware('can:products.edit')->group(function () {
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('products/{product}/mark-damaged', [ProductController::class, 'markDamaged'])->name('products.mark-damaged');
    Route::post('products/{product}/restore-damaged', [ProductController::class, 'restoreDamaged'])->name('products.restore-damaged');
    Route::post('products/{product}/restock', [ProductController::class, 'restock'])->name('products.restock');
});
    Route::middleware('can:products.delete')->group(function () {
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });


    // ------------------------------------------------------------------
    // Finance sub-menus: balance (+ shareholders), transaction, advance.
    // Static "create" routes are registered before their wildcards.
    // ------------------------------------------------------------------
    Route::view('/finance', 'pages.coming-soon')->name('finance.index');

    // Balance
  Route::middleware('can:balance.create')->group(function () {
    Route::post('finance/balance/transfer', [BalanceController::class, 'transfer'])->name('finance.balance.transfer');
    Route::post('finance/balance/deposit', [BalanceController::class, 'deposit'])->name('finance.balance.deposit');
    Route::post('finance/balance/adjust', [BalanceController::class, 'adjust'])->name('finance.balance.adjust');
    Route::put('finance/balance/transactions/{transaction}', [BalanceController::class, 'updateEntry'])->name('finance.balance.transactions.update');
    Route::post('finance/balance/accounts', [BalanceController::class, 'storeAccount'])->name('finance.balance.accounts.store');
    Route::put('finance/balance/accounts/{account}', [BalanceController::class, 'updateAccount'])->name('finance.balance.accounts.update');
    Route::post('finance/shareholders', [ShareholderController::class, 'store'])->name('finance.shareholders.store');
    Route::post('finance/shareholders/transactions', [ShareholderController::class, 'storeTransaction'])->name('finance.shareholders.transactions.store');
});
    Route::middleware('can:balance.view')->group(function () {
        Route::get('finance/balance', [BalanceController::class, 'index'])->name('finance.balance');
        Route::get('finance/shareholders/{shareholder}/transactions', [ShareholderController::class, 'transactions'])->name('finance.shareholders.transactions');
    });
    Route::middleware('can:balance.delete')->group(function () {
        Route::delete('finance/shareholders/{shareholder}', [ShareholderController::class, 'destroy'])->name('finance.shareholders.destroy');
        Route::delete('finance/shareholders/transactions/{shareholderTransaction}', [ShareholderController::class, 'destroyTransaction'])->name('finance.shareholders.transactions.destroy');
Route::delete('finance/balance/accounts/{account}', [BalanceController::class, 'destroyAccount'])->name('finance.balance.accounts.destroy');        
});
    // Transaction
    Route::middleware('can:transaction.create')->group(function () {
        Route::get('finance/transaction/create', [TransactionController::class, 'create'])->name('finance.transaction.create');
        Route::post('finance/transaction', [TransactionController::class, 'store'])->name('finance.transaction.store');
    });
    Route::middleware('can:transaction.view')->group(function () {
        Route::get('finance/transaction', [TransactionController::class, 'index'])->name('finance.transaction');
        Route::get('finance/transaction/{transaction}', [TransactionController::class, 'show'])->name('finance.transaction.show');
    });
    Route::middleware('can:transaction.delete')->group(function () {
        Route::delete('finance/transaction/{transaction}', [TransactionController::class, 'destroy'])->name('finance.transaction.destroy');
    });

    // Advance
    Route::middleware('can:advance.create')->group(function () {
        Route::post('finance/advance', [AdvanceController::class, 'store'])->name('finance.advance.store');
    });
    Route::middleware('can:advance.view')->group(function () {
        Route::get('finance/advance', [AdvanceController::class, 'index'])->name('finance.advance');
        Route::get('finance/employees/{employee}/transactions', [AdvanceController::class, 'employeeTransactions'])->name('finance.employee.transactions');
    });
    Route::middleware('can:advance.delete')->group(function () {
        Route::delete('finance/advance/{advance}', [AdvanceController::class, 'destroy'])->name('finance.advance.destroy');
    });

    // ------------------------------------------------------------------
    // Employees
    // ------------------------------------------------------------------
    Route::middleware('can:employees.view')->group(function () {
        Route::view('/employees', 'pages.coming-soon')->name('employees.index');
    });
    Route::middleware('can:employees.create')->group(function () {
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    });
    Route::middleware('can:employees.delete')->group(function () {
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    // ------------------------------------------------------------------
    // Purchases (create before {purchase})
    // ------------------------------------------------------------------
    Route::middleware('can:purchases.create')->group(function () {
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    });
    Route::middleware('can:purchases.view')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });
    Route::middleware('can:purchases.edit')->group(function () {
        Route::post('/purchases/{purchase}/arrive', [PurchaseController::class, 'arrive'])->name('purchases.arrive');
        Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    });
    Route::middleware('can:purchases.delete')->group(function () {
        Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });

    // ------------------------------------------------------------------
    // Reports
    // ------------------------------------------------------------------
    Route::prefix('reports')->name('reports.')->middleware('can:reports.view')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('profit', [ReportController::class, 'profit'])->name('profit');
        Route::get('product-profit', [ReportController::class, 'productProfit'])->name('product-profit');
        Route::get('expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('export/pdf', [ReportController::class, 'exportIndexPdf'])->name('index.exportPdf');
        Route::get('export/excel', [ReportController::class, 'exportIndexExcel'])->name('index.exportExcel');
        Route::get('export/csv', [ReportController::class, 'exportIndexCsv'])->name('index.exportCsv');
        Route::get('sales/export/pdf', [ReportController::class, 'exportSalesPdf'])->name('sales.exportPdf');
        Route::get('sales/export/excel', [ReportController::class, 'exportSalesExcel'])->name('sales.exportExcel');
        Route::get('profit/export/pdf', [ReportController::class, 'exportProfitPdf'])->name('profit.exportPdf');
        Route::get('profit/export/excel', [ReportController::class, 'exportProfitExcel'])->name('profit.exportExcel');
        Route::get('product-profit/export/pdf', [ReportController::class, 'exportProductProfitPdf'])->name('product-profit.exportPdf');
        Route::get('product-profit/export/excel', [ReportController::class, 'exportProductProfitExcel'])->name('product-profit.exportExcel');
        Route::get('expenses/export/pdf', [ReportController::class, 'exportExpensesPdf'])->name('expenses.exportPdf');
        Route::get('expenses/export/excel', [ReportController::class, 'exportExpensesExcel'])->name('expenses.exportExcel');
      Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
Route::get('purchases/export/pdf', [ReportController::class, 'exportPurchasesPdf'])->name('purchases.exportPdf');
Route::get('purchases/export/excel', [ReportController::class, 'exportPurchasesExcel'])->name('purchases.exportExcel');
});

        
    // Profile (unrestricted — every logged-in user manages their own)
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
});

require __DIR__.'/auth.php';
