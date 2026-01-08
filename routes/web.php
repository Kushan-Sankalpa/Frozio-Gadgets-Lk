<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\SmsGatewayController;
use App\Http\Controllers\TierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AppointmentReportsController;
use App\Http\Controllers\ClientReportController;
use App\Http\Controllers\SalesWithTeamController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('auth/Login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/privacy-policy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacypolicy');

Route::get('/test-error/{code}', function ($code) {
    abort((int)$code);
});

Route::middleware('auth')->group(function () {

    Route::get('/notifications/list', function (Request $request) {
        $user = $request->user();

        $perPage = $request->query('per_page', 15);

        $paginated = $user->notifications()
            ->latest()
            ->paginate($perPage)
            ->through(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->data['title'] ?? 'Notification',
                'message'    => $n->data['message'] ?? '',
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return $paginated;
    })->name('notifications.list');

    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        Log::info($notification);
        $notification->markAsRead();

        return ['status' => 'ok'];
    })->name('notifications.read');

    Route::post('/notifications/read-all', function (Request $request) {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return ['status' => 'ok'];
    })->name('notifications.read-all');

    Route::get('/notifications', function (Request $request) {
        return inertia('Notifications/Index');
    })->name('notifications.page');
});


// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');


    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->middleware(['can:user.view'])->name('users');
        Route::get('/get/data', [UsersController::class, 'getData'])->middleware(['can:user.view'])->name('users.getdata');
        Route::get('/create', [UsersController::class, 'create'])->middleware(['can:user.create'])->name('users.create');
        Route::post('/store', [UsersController::class, 'store'])->middleware(['can:user.create'])->name('users.store');
        Route::get('/edit/{id}', [UsersController::class, 'edit'])->middleware(['can:user.edit'])->name('users.edit');
        Route::post('/update', [UsersController::class, 'update'])->middleware(['can:user.edit'])->name('users.update');
        Route::put('/update/status', [UsersController::class, 'updateStatus'])->middleware(['can:user.edit'])->name('users.change.status');
        Route::put('/update/password', [UsersController::class, 'updatePassword'])->middleware(['can:user.edit'])->name('users.change.password');
        Route::post('/suspend', [UsersController::class, 'softDelete'])->middleware(['can:user.delete'])->name('users.delete');
        Route::post('/restore', [UsersController::class, 'restore'])->middleware(['can:user.delete'])->name('users.restore');
        Route::post('/delete', [UsersController::class, 'delete'])->middleware(['can:user.delete'])->name('users.remove');

        Route::get('/order/list', [UsersController::class, 'orderList'])->name('users.order.list');
        Route::post('/order/update', [UsersController::class, 'orderUpdate'])->name('users.order.update');
    });


    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware(['can:roles-permissions.view'])->name('roles');
        Route::get('/getdata', [RoleController::class, 'getdata'])->middleware(['can:roles-permissions.view'])->name('roles.getdata');
        Route::get('/create', [RoleController::class, 'create'])->middleware(['can:roles-permissions.create'])->name('roles.create');
        Route::post('/store', [RoleController::class, 'store'])->middleware(['can:roles-permissions.create'])->name('roles.store');
        Route::get('/edit/{id}', [RoleController::class, 'edit'])->middleware(['can:roles-permissions.edit'])->name('roles.edit');
        Route::post('/update', [RoleController::class, 'update'])->middleware(['can:roles-permissions.edit'])->name('roles.update');
        Route::get('/permissions/{id}', [RoleController::class, 'editPermissions'])->middleware(['can:roles-permissions.edit'])->name('roles.permissions');
        Route::post('/permissions/update', [RoleController::class, 'updatePermissions'])->middleware(['can:roles-permissions.edit'])->name('roles.permissions.update');
        Route::delete('/delete/{id}', [RoleController::class, 'destroy'])->middleware(['can:roles-permissions.delete'])->name('roles.destroy');
    });

    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->middleware(['can:calendar.view'])->name('calendar.index');
        Route::get('/data', [CalendarController::class, 'staffdata'])->name('calendar.data');
                Route::get('/servicesdata', [CalendarController::class, 'servicesdata'])->name('calendar.servicesdata');

    });


    // Branches
    Route::prefix('branch')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->middleware(['can:branch.view'])->name('branch.index');
        Route::get('/getdata', [BranchController::class, 'getdata'])->middleware(['can:branch.view'])->name('branch.getdata');
        Route::get('/create', [BranchController::class, 'create'])->middleware(['can:branch.create'])->name('branch.create');
        Route::post('/store', [BranchController::class, 'store'])->middleware(['can:branch.create'])->name('branch.store');
        Route::get('/edit/{id}', [BranchController::class, 'edit'])->middleware(['can:branch.edit'])->name('branch.edit');
        Route::post('/update', [BranchController::class, 'update'])->middleware(['can:branch.edit'])->name('branch.update');
        Route::delete('/branch/{id}', [BranchController::class, 'destroy'])->middleware(['can:branch.delete'])->name('branch.destroy');
        Route::post('/branch/{id}/toggle-status', [BranchController::class, 'toggleStatus'])
            ->name('branch.toggleStatus');
    });

    Route::prefix('service')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->middleware(['can:service.view'])->name('service.index');
        Route::get('/getdata', [ServiceController::class, 'getdata'])->middleware(['can:service.view'])->name('service.getdata');
        Route::get('/create', [ServiceController::class, 'create'])->middleware(['can:service.create'])->name('service.create');
        Route::post('/store', [ServiceController::class, 'store'])->middleware(['can:service.create'])->name('service.store');
        Route::get('/edit/{id}', [ServiceController::class, 'edit'])->middleware(['can:service.edit'])->name('service.edit');
        Route::post('/update', [ServiceController::class, 'update'])->middleware(['can:service.edit'])->name('service.update');
        Route::post('/delete', [ServiceController::class, 'destroy'])->middleware(['can:service.delete'])->name('service.delete');
        Route::put('/status/update', [ServiceController::class, 'updateStatus'])->middleware(['can:service.edit'])->name('service.status.update');
        Route::get('/categories/getdata', [ServiceController::class, 'categoryGetdata'])->middleware(['can:service.view'])->name('service.category.getdata');
        Route::put('/category/status/update', [ServiceController::class, 'categoryStatusUpdate'])->middleware(['can:service.edit'])->name('service.category.status.update');
        Route::post('/category/store', [ServiceController::class, 'categoryStore'])->middleware(['can:service.create'])->name('service.category.store');
        Route::post('/category/update', [ServiceController::class, 'categoryUpdate'])->middleware(['can:service.edit'])->name('service.category.update');
        Route::post('/categories/destroy', [ServiceController::class, 'categoryDestroy'])->name('service.category.destroy');
        Route::get('/{serviceId}/variants/getdata', [ServiceController::class, 'variantGetdata'])->middleware(['can:service.view'])->name('service.variant.getdata');
        Route::post('/variant/store', [ServiceController::class, 'variantStore'])->middleware(['can:service.edit'])->name('service.variant.store');
        Route::post('/variant/update', [ServiceController::class, 'variantUpdate'])->middleware(['can:service.edit'])->name('service.variant.update');
        Route::post('/variant/delete', [ServiceController::class, 'variantDestroy'])->middleware(['can:service.edit'])->name('service.variant.delete');
        Route::post('/reorder', [ServiceController::class, 'reorder'])->name('service.reorder');
    });


    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->middleware(['can:clients.view'])->name('clients.index');
        Route::get('/getdata', [ClientController::class, 'getdata'])->middleware(['can:clients.view'])->name('clients.getdata');
        Route::get('/create', [ClientController::class, 'create'])->middleware(['can:clients.create'])->name('clients.create');
        Route::post('/', [ClientController::class, 'store'])->middleware(['can:clients.store'])->name('clients.store');
        Route::get('/{id}/edit', [ClientController::class, 'edit'])->middleware(['can:clients.edit'])->name('clients.edit');
        Route::post('/update', [ClientController::class, 'update'])->middleware(['can:clients.update'])->name('clients.update');
        Route::put('/update/password', [ClientController::class, 'updatePassword'])->middleware(['can:clients.update'])->name('clients.change.password');
        Route::delete('/{id}', [ClientController::class, 'destroy'])->middleware(['can:clients.delete'])->name('clients.destroy');
        Route::get('/show/{client}', [ClientController::class, 'show'])->middleware(['can:clients.view'])->name('clients.show');
        Route::post('/{client}/add-points', [ClientController::class, 'addPoints'])->middleware(['can:clients.edit'])->name('clients.addPoints');
    });



    Route::prefix('discounts')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->middleware(['can:discounts.view'])->name('discounts');
        Route::get('/getdata', [DiscountController::class, 'getdata'])->middleware(['can:discounts.view'])->name('discounts.getdata');
        Route::get('/create', [DiscountController::class, 'create'])->middleware(['can:discounts.create'])->name('discounts.create');
        Route::post('/store', [DiscountController::class, 'store'])->middleware(['can:discounts.create'])->name('discounts.store');
        Route::get('/edit/{id}', [DiscountController::class, 'edit'])->middleware(['can:discounts.edit'])->name('discounts.edit');
        Route::post('/update', [DiscountController::class, 'update'])->middleware(['can:discounts.edit'])->name('discounts.update');
        Route::delete('/{id}', [DiscountController::class, 'destroy'])->middleware(['can:discounts.delete'])->name('discounts.destroy');
        Route::post('/{id}/toggle-status', [DiscountController::class, 'toggleStatus'])->middleware(['can:discounts.edit'])
            ->name('discounts.toggleStatus');
    });
    Route::prefix('banner')->group(function () {
        Route::get('/', [BannerController::class, 'index'])->middleware(['can:banner.view'])->name('banner.index');
        Route::get('/create', [BannerController::class, 'create'])->middleware(['can:banner.create'])->name('banner.create');
        Route::post('/store', [BannerController::class, 'store'])->middleware(['can:banner.create'])->name('banner.store');
        Route::get('/edit/{id}', [BannerController::class, 'edit'])->middleware(['can:banner.edit'])->name('banner.edit');
        Route::post('/update', [BannerController::class, 'update'])->middleware(['can:banner.edit'])->name('banner.update');
        Route::delete('/{id}', [BannerController::class, 'destroy'])->middleware(['can:banner.delete'])->name('banner.destroy');
        Route::post('/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('banner.toggleStatus');
    });

    Route::prefix('loyalty-program')->group(function () {
        Route::get('/', [TierController::class, 'index'])->middleware(['can:loyalty-program.view'])->name('loyalty-program.index');
        Route::get('/getdata', [TierController::class, 'getdata'])->middleware(['can:loyalty-program.view'])->name('loyalty-program.getdata');
        Route::get('/create', [TierController::class, 'create'])->middleware(['can:loyalty-program.create'])->name('loyalty-program.create');
        Route::post('/store', [TierController::class, 'store'])->middleware(['can:loyalty-program.create'])->name('loyalty-program.store');
        Route::get('/edit/{id}', [TierController::class, 'edit'])->middleware(['can:loyalty-program.edit'])->name('loyalty-program.edit');
        Route::post('/update', [TierController::class, 'update'])->middleware(['can:loyalty-program.edit'])->name('loyalty-program.update');
        Route::post('/delete', [TierController::class, 'destroy'])->middleware(['can:loyalty-program.delete'])->name('loyalty-program.delete');
        Route::put('/status/update', [TierController::class, 'updateStatus'])->middleware(['can:loyalty-program.edit'])->name('loyalty-program.status.update');
    });


    Route::prefix('sms-gateway')->group(function () {
        Route::get('/', [SmsGatewayController::class, 'index'])->middleware(['can:sms-gateway.view'])->name('sms.gateway');
        Route::post('/store-or-update', [SmsGatewayController::class, 'storeOrUpdate'])->middleware(['can:sms-gateway.create'])->name('sms.gateway.save');
        Route::post('/test', [SmsGatewayController::class, 'testSMS'])->middleware(['can:sms-gateway.test'])->name('sms.gateway.test');
    });

    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [BookingController::class, 'index'])->middleware(['can:bookings.view'])->name('bookings.index');
    Route::get('/getdata', [BookingController::class, 'getdata'])->middleware(['can:bookings.view'])->name('bookings.getdata');
    Route::post('/booking-sales', [BookingController::class, 'storeSale'])->name('booking.sales.store');
    Route::get('/bookings/{booking}/details', [BookingController::class, 'showDetails'])->name('bookings.details');
    Route::post('/bookings/{booking}/payment-pending', [BookingController::class, 'markPaymentPending'])->name('bookings.mark-payment-pending');
    Route::post('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::post('/bookings/blocked-time', [BookingController::class, 'storeBlockedTime'])->name('bookings.blocked-time.store');
    Route::put('/bookings/blocked-time/{booking}', [BookingController::class, 'updateBlockedTime'])->name('bookings.blocked-time.update');
    Route::post('/bookings/{booking}/email-receipt', [BookingController::class, 'emailReceipt'])->name('bookings.email-receipt');
    Route::get('/bookings/pending', [BookingController::class, 'waitlistpending'])->middleware(['can:waitlist.view'])->name('bookings.pending');

    Route::get('/bookings/{booking}/waitlist', [BookingController::class, 'waitlistShow'])->middleware(['can:waitlist.view'])->name('bookings.waitlist.show');
    Route::put('/bookings/{booking}/waitlist', [BookingController::class, 'waitlistUpdate'])->middleware(['can:waitlist.accept'])->name('bookings.waitlist.update');
    Route::get('/branches/select', [BookingController::class, 'branchSelect'])->name('branches.select');
    Route::get('/employees/select', [BookingController::class, 'employeeSelect'])->name('employees.select');
    Route::put('/bookings/blocked-time/{booking}', [BookingController::class, 'updateBlockedTime'])->name('bookings.blocked-time.update');
    Route::post('/bookings/{booking}/sync-services', [BookingController::class, 'syncServicesFromTip'])->name('booking.services.sync');
    Route::post('/bookings/{booking}/client', [BookingController::class, 'updateClient'])->name('bookings.update-client');
    Route::post('/bookings/{booking}/note', [BookingController::class, 'updateNote'])->name('bookings.update-note');
    Route::get('/discounts/select', [DiscountController::class, 'select'])->name('discounts.select');


    Route::delete('/bookings/{booking}/note', [BookingController::class, 'deleteNote'])->name('bookings.delete-note');






    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->middleware(['can:reports.view'])->name('reports.index');
        Route::get('/getdata', [ReportController::class, 'getdata'])->middleware(['can:reports.view'])->name('reports.getdata');
        Route::get('/create', [ReportController::class, 'create'])->middleware(['can:reports.create'])->name('reports.create');
        Route::post('/', [ReportController::class, 'store'])->middleware(['can:reports.create'])->name('reports.store');
        Route::get('/{id}/edit', [ReportController::class, 'edit'])->middleware(['can:reports.edit'])->name('reports.edit');
        Route::post('/update', [ReportController::class, 'update'])->middleware(['can:reports.edit'])->name('reports.update');
        Route::delete('/{id}', [ReportController::class, 'destroy'])->middleware(['can:reports.delete'])->name('reports.destroy');
        Route::get('/revenue', [ReportController::class, 'revenue'])->middleware(['can:reports.view'])->name('reports.revenue');
        Route::get('/revenue/pdf', [ReportController::class, 'revenuePdf'])->middleware(['can:reports.view'])->name('reports.revenue.pdf');
        Route::get('/reports/revenue/excel', [ReportController::class, 'revenueExcel'])->name('reports.revenue.excel');


        Route::get('/daily-sales', [ReportController::class, 'dailySales'])
            ->middleware(['can:reports.view'])
            ->name('reports.daily_sales');

        Route::get('/daily-sales/pdf', [ReportController::class, 'dailySalesPdf'])
            ->middleware(['can:reports.view'])
            ->name('reports.daily_sales.pdf');

        Route::get('/daily-sales/excel', [ReportController::class, 'dailySalesExcel'])
            ->middleware(['can:reports.view'])
            ->name('reports.daily_sales.excel');
    });
});

Route::get('/invoice/{sale}', [PublicInvoiceController::class, 'show'])
    ->name('public.invoice.show')
    ->middleware(['signed', 'throttle:60,1']);

Route::get('/invoice/{sale}/pdf', [PublicInvoiceController::class, 'pdf'])
    ->name('public.invoice.pdf')
    ->middleware(['signed', 'throttle:60,1']);



Route::get('/sales/{sale}/edit', [BookingController::class, 'editSale'])
    ->middleware(['can:sales.edit'])
    ->name('sales.edit');

Route::patch('/sales/{sale}', [BookingController::class, 'updateSale'])
    ->middleware(['can:sales.edit'])
    ->name('sales.update');

//Appoimnets summary report  

Route::get('/appointments/summary', [AppointmentReportsController::class, 'summary'])
    ->middleware(['can:reports.view'])
    ->name('reports.appointments.summary');

Route::get('/appointments/summary/pdf', [AppointmentReportsController::class, 'summaryPdf'])
    ->middleware(['can:reports.view'])
    ->name('reports.appointments.summary.pdf');

Route::get('/appointments/summary/excel', [AppointmentReportsController::class, 'summaryExcel'])
    ->middleware(['can:reports.view'])
    ->name('reports.appointments.summary.excel');



// Appointments list report
Route::get('/appointments/list', [AppointmentReportsController::class, 'list'])
    ->middleware(['can:reports.view'])
    ->name('reports.appointments.list');

Route::get('/appointments/list/pdf', [AppointmentReportsController::class, 'listPdf'])
    ->middleware(['can:reports.view'])
    ->name('reports.appointments.list.pdf');

Route::get('/appointments/list/excel', [AppointmentReportsController::class, 'listExcel'])
    ->middleware(['can:reports.view'])
    ->name('reports.appointments.list.excel');




// Client list report
Route::get('/reports/clients/list', [ClientReportController::class, 'list'])
    ->middleware(['can:reports.view'])
    ->name('reports.clients.list');

Route::get('/reports/clients/list/pdf', [ClientReportController::class, 'listPdf'])
    ->middleware(['can:reports.view'])
    ->name('reports.clients.list.pdf');

Route::get('/reports/clients/list/excel', [ClientReportController::class, 'listExcel'])
    ->middleware(['can:reports.view'])
    ->name('reports.clients.list.excel');




Route::prefix('reports/sales-team')->name('reports.sales_team.')->group(function () {
    // Daily 
    Route::get('/daily', [SalesWithTeamController::class, 'daily'])->name('daily');
    Route::get('/daily/pdf', [SalesWithTeamController::class, 'dailyPdf'])->name('daily.pdf');
    Route::get('/daily/excel', [SalesWithTeamController::class, 'dailyExcel'])->name('daily.excel');

    // Monthly 
    Route::get('/monthly', [SalesWithTeamController::class, 'monthly'])->name('monthly');
    Route::get('/monthly/pdf', [SalesWithTeamController::class, 'monthlyPdf'])->name('monthly.pdf');
    Route::get('/monthly/excel', [SalesWithTeamController::class, 'monthlyExcel'])->name('monthly.excel');
});
