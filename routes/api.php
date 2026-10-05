<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\LogisticController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/testimonial/customer/{uuid}', [UserController::class, 'updateTestimonialCustomer']);
Route::get('/landingPage', [PackageController::class, 'landingPage']);

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/forgotpassword/{token}', 'Forgotpassword');
    Route::post('/sendForgotpassword', 'sendForgotpassword');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum', 'restrictStatus:true']], function () {
    Route::get('/user', [UserController::class, 'indexProfile']);
    Route::put('/user', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);

    Route::middleware(['restrictRole:Superadmin'])->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('/employee', 'indexUser');
            Route::post('/employee', 'storeUser');
            Route::get('/employee/{uuid}', 'showUser');
            Route::put('/employee/{uuid}', 'updateUser');
            Route::put('/employee/{uuid}/password', 'updatePasswordByAdmin');
            Route::put('/employee/{uuid}/role', 'updateUserRole');
            Route::put('/employee/{uuid}/reactivate', 'reactiveUser');
            Route::put('/employee/{uuid}/suspend', 'suspendUser');

            Route::get('/role', 'indexRole');
            Route::get('/role/{uuid}', 'showRole');

            Route::get('/activity', 'indexActivities');
            Route::get('/activity/{uuid}', 'showActivity');
        });
        Route::get('/dashboard', [UserController::class, 'dashboardSuperAdmin']);

        Route::get('/financialRecordSA', [FinanceController::class, 'indexFinancialRecordSA']);
        Route::get('/financialRecordSA/{uuidOrder}', [FinanceController::class, 'showRecordSA']);
    });

    Route::middleware(['restrictRole:Admin'])->group(function () {
        Route::get('/dashboardAdmin', [UserController::class, 'dashboardAdmin']);
    });
    Route::middleware(['restrictRole:Akuntan'])->group(function () {
        Route::get('/dashboardFinance', [UserController::class, 'dashboardFinance']);
    });

    Route::middleware(['restrictRole:Superadmin,Admin,Akuntan'])->group(function () {
        //Menu Category
        Route::get('/menu/category', [MenuController::class, 'indexCategory']);
        Route::post('/menu/category', [MenuController::class, 'storeCategory']);
        Route::get('/menu/category/{uuid}', [MenuController::class, 'showCategory']);
        Route::put('/menu/category/{uuid}', [MenuController::class, 'updateCategory']);
        Route::delete('/menu/category/{uuid}', [MenuController::class, 'destroyCategory']);

        //Menu Recipes
        Route::get('/menu/recipe/{uuidMenu}', [MenuController::class, 'indexRecipes']);
        Route::post('/menu/recipe/{uuidMenu}', [MenuController::class, 'updateRecipes']);
        Route::delete('/menu/recipe/{uuid}', [MenuController::class, 'destroyRecipes']);

        //Menu
        Route::get('/menu', [MenuController::class, 'index']);
        Route::post('/menu', [MenuController::class, 'store']);
        Route::get('/menu/{uuid}', [MenuController::class, 'show']);
        Route::post('/menu/{uuid}', [MenuController::class, 'update']);
        Route::delete('/menu/{uuid}', [MenuController::class, 'destroy']);

        Route::get('/package/getDataMenuItemPackage/{uuid}', [MenuController::class, 'showOrder']);

        //Package
        Route::get('/package', [PackageController::class, 'index']);
        Route::post('/package', [PackageController::class, 'store']);
        Route::get('/package/{uuid}', [PackageController::class, 'show']);
        Route::put('/package/{uuid}', [PackageController::class, 'update']);
        Route::delete('/package/{uuid}', [PackageController::class, 'destroy']);

        //Package Data
        Route::get('/package/package-data/{uuidPackage}', [PackageController::class, 'indexPackageData']);
        Route::post('/package/package-data/{uuidPackage}', [PackageController::class, 'storePackageData']);
        Route::get('/package/package-data/detail/{uuid}', [PackageController::class, 'showPackageData']);
        Route::put('/package/package-data/{uuidPackage}', [PackageController::class, 'updatePackageData']);
        Route::delete('/package/package-data/{uuid}', [PackageController::class, 'destroyPackageData']);

        //Package Stall
        Route::get('/package/package-stall/{uuidPackage}', [PackageController::class, 'indexPackageStall']);
        Route::post('/package/package-stall/{uuidPackage}', [PackageController::class, 'storePackageStall']);
        Route::get('/package/package-stall/detail/{uuid}', [PackageController::class, 'showPackageStall']);
        Route::put('/package/package-stall/{uuidPackage}', [PackageController::class, 'updatePackageStall']);
        Route::delete('/package/package-stall/{uuid}', [PackageController::class, 'destroyPackageStall']);

        // Customer
        Route::get('/customer', [CustomerController::class, 'index']);
        Route::post('/customer', [CustomerController::class, 'store']);
        Route::get('/customer/{uuid}', [CustomerController::class, 'show']);
        Route::put('/customer/{uuid}', [CustomerController::class, 'update']);
        Route::delete('/customer/{uuid}', [CustomerController::class, 'destroy']);
        Route::get('/customer/search/{name}', [CustomerController::class, 'search']);

        // testimoni
        Route::get('/testimonial', [UserController::class, 'indexTestimonial']);
        Route::post('/testimonial', [UserController::class, 'storeTestimonial']);
        Route::get('/testimonial/{uuid}', [UserController::class, 'showTestimonial']);
        Route::put('/testimonial/{uuid}', [UserController::class, 'updateTestimonial']);
        Route::delete('/testimonial/{uuid}', [UserController::class, 'destroyTestimonial']);

        //Order
        // Route::get('/order', [OrderController::class, 'index']);
        Route::post('/order', [OrderController::class, 'storeWithCustomer']);
        // Route::get('/order/{uuid}', [OrderController::class, 'show']);
        Route::put('/order/{uuid}', [OrderController::class, 'update']);
        Route::delete('/order/{uuid}', [OrderController::class, 'destroy']);
        // Route::post('/order/eventEnd/{uuid}', [OrderController::class, 'eventEnd']);

        Route::post('/order/fieldCoordinator/{uuidOrder}', [OrderController::class, 'updateFieldCoordinator']);

        //Order Items
        // Route::get('/order/menu/{uuidOrder}', [OrderController::class, 'indexMenus']);
        Route::post('/order/menu/{uuidOrder}/list-menu', [OrderController::class, 'listMenu']);
        Route::post('/order/menu/{uuidOrder}/add-menu', [OrderController::class, 'addMenus']);
        Route::post('/order/menu/{uuidOrder}', [OrderController::class, 'storeMenus']);
        // Route::get('/order/menu/detail/{uuidOrderPackage}', [OrderController::class, 'showMenus']);
        Route::post('/order/menu/update/{uuidOrder}', [OrderController::class, 'updateMenus']);
        // Route::post('/order/menu/refresh/{uuidOrder}', [OrderController::class, 'refreshMenu']);
        // Route::delete('/order/menu/{uuid}', [OrderController::class, 'destroyMenus']);

        // Route::get('/order/menu/restore/package/{uuidOrderPackage}', [OrderController::class, 'restorePackage']);
        Route::get('/order/menu/restore/packageMenu/{uuidOrderPackageMenu}', [OrderController::class, 'restorePackageMenu']);
        Route::get('/order/menu/restore/customMenu/{uuidOrderCustomMenu}', [OrderController::class, 'restoreCustomMenu']);

        //event logistic items
        // Route::get('/order/item/{uuidOrder}', [OrderController::class, 'indexLogistic']);
        Route::post('/order/item/{uuidOrder}', [OrderController::class, 'storeLogistic']);
        Route::put('/order/item/{uuidOrder}', [OrderController::class, 'updateLogistic']);
        Route::delete('/order/item/{uuid}', [OrderController::class, 'destroyLogistic']);

        // purchase plan
        Route::get('/order/purchasePlan/{uuidOrder}', [OrderController::class, 'readPurchasePlan']);

        // order Staff
        // Route::get('/order/staff/{uuidOrder}', [OrderController::class, 'indexEventStaff']);
        Route::post('/order/staff/{uuidOrder}', [OrderController::class, 'storeEventStaff']);
        // Route::get('/order/staff/detail/{uuid}', [OrderController::class, 'showEventStaff']);
        Route::put('/order/staff/{uuidOrder}', [OrderController::class, 'updateEventStaff']);
        Route::delete('/order/staff/detail/{uuid}', [OrderController::class, 'destroyEventStaff']);

        //Payment
        Route::get('/payment', [FinanceController::class, 'index']);
        Route::get('/payment/{uuid}', [FinanceController::class, 'show']);
        Route::put('/payment/{uuid}', [FinanceController::class, 'update']);
        Route::delete('/payment/{uuid}', [FinanceController::class, 'destroy']);

        //Payment Installment
        Route::get('/payment/installment/{uuidPayment}', [FinanceController::class, 'indexInstallment']);
        Route::post('/payment/installment/{uuidPayment}', [FinanceController::class, 'storeInstallment']);
        Route::get('/payment/installment/detail/{uuid}', [FinanceController::class, 'showInstallment']);
        Route::post('/payment/installment/update/{uuid}', [FinanceController::class, 'updateInstallment']);
        Route::delete('/payment/installment/{uuid}', [FinanceController::class, 'destroyInstallment']);

        //Bank
        Route::get('/bank', [FinanceController::class, 'indexBank']);
        Route::post('/bank', [FinanceController::class, 'storeBank']);
        Route::get('/bank/{uuid}', [FinanceController::class, 'showBank']);
        Route::put('/bank/{uuid}', [FinanceController::class, 'updateBank']);
        Route::delete('/bank/{uuid}', [FinanceController::class, 'destroyBank']);

        //Financial Record
        Route::get('/order/record/{uuidOrder}', [FinanceController::class, 'indexRecord']);
        Route::post('/order/record/{uuidOrder}', [FinanceController::class, 'storeRecord']);
        Route::post('/order/record/{uuidPayment}/refund', [FinanceController::class, 'storeRefund']);
        Route::get('/order/record/detail/{uuid}', [FinanceController::class, 'showRecord']);
        Route::post('/order/record/update/{uuid}', [FinanceController::class, 'updateRecord']);
        Route::delete('/order/record/{uuid}', [FinanceController::class, 'destroyRecord']);

        Route::get('/financialRecord', [FinanceController::class, 'indexFinancialRecord']);
        Route::get('/financialRecord/income/{uuid}', [FinanceController::class, 'showRecordIncome']);
        Route::get('/financialRecord/outcome/{uuid}', [FinanceController::class, 'showRecordOutcome']);


        //Meeting schedule
        Route::get('/meeting-schedule', [UserController::class, 'indexMeet']);
        Route::post('/meeting-schedule', [UserController::class, 'storeMeet']);
        Route::get('/meeting-schedule/{uuid}', [UserController::class, 'showMeet']);
        Route::put('/meeting-schedule/{uuid}', [UserController::class, 'updateMeet']);
        Route::delete('/meeting-schedule/{uuid}', [UserController::class, 'destroyMeet']);

        //Staff
        Route::get('/staff', [UserController::class, 'indexStaff']);
        Route::post('/staff', [UserController::class, 'storeStaff']);
        Route::get('/staff/{uuid}', [UserController::class, 'showStaff']);
        Route::put('/staff/{uuid}', [UserController::class, 'updateStaff']);
        Route::delete('/staff/{uuid}', [UserController::class, 'destroyStaff']);

        // //inventory
        // Route::get('/inventory', [LogisticController::class, 'indexInventory']);
        // Route::post('/inventory', [LogisticController::class, 'storeInventory']);
        // Route::get('/inventory/{uuid}', [LogisticController::class, 'showInventory']);
        // Route::put('/inventory/{uuid}', [LogisticController::class, 'updateInventory']);
        // Route::delete('/inventory/{uuid}', [LogisticController::class, 'destroyInventory']);

        // //transfer
        // Route::get('/transfer', [LogisticController::class, 'indexTransfer']);
        // Route::post('/transfer', [LogisticController::class, 'storeTransfer']);
        // Route::get('/transfer/{uuid}', [LogisticController::class, 'showTransfer']);
        // Route::put('/transfer/{uuid}', [LogisticController::class, 'updateTransfer']);
        // Route::delete('/transfer/{uuid}', [LogisticController::class, 'destroyTransfer']);

        // Route::post('/requestTransfer/{uuidOrder}', [LogisticController::class, 'requestTransfer']);
    });

    Route::get('/order', [OrderController::class, 'index']);
    Route::get('/order/{uuid}', [OrderController::class, 'show']);
    Route::post('/order/eventEnd/{uuid}', [OrderController::class, 'eventEnd']);

    //Order Items
    Route::get('/order/menu/{uuidOrder}', [OrderController::class, 'indexMenus']);
    Route::get('/order/menu/detail/{uuidOrderPackage}', [OrderController::class, 'showMenus']);

    //event logistic items
    Route::get('/order/item/{uuidOrder}', [OrderController::class, 'indexLogistic']);

    // order Staff
    Route::get('/order/staff/{uuidOrder}', [OrderController::class, 'indexEventStaff']);
    Route::get('/order/staff/detail/{uuid}', [OrderController::class, 'showEventStaff']);

    //inventory
    Route::get('/inventory', [LogisticController::class, 'indexInventory']);
    Route::post('/inventory', [LogisticController::class, 'storeInventory']);
    Route::get('/inventory/{uuid}', [LogisticController::class, 'showInventory']);
    Route::put('/inventory/{uuid}', [LogisticController::class, 'updateInventory']);
    Route::delete('/inventory/{uuid}', [LogisticController::class, 'destroyInventory']);

    //transfer
    Route::get('/transfer', [LogisticController::class, 'indexTransfer']);
    Route::post('/transfer', [LogisticController::class, 'storeTransfer']);
    Route::get('/transfer/{uuid}', [LogisticController::class, 'showTransfer']);
    Route::put('/transfer/{uuid}', [LogisticController::class, 'updateTransfer']);
    Route::delete('/transfer/{uuid}', [LogisticController::class, 'destroyTransfer']);

    Route::post('/requestTransfer/{uuidOrder}', [LogisticController::class, 'requestTransfer']);

    // print pdf
    Route::get('/order/{uuid}/print/{type?}', [OrderController::class, 'orderPrint']);
    Route::get('/payment/installment/{uuidPayment}/print', [FinanceController::class, 'invoicePrint']);
    Route::get('/payment/installment/detail/{uuid}/print', [FinanceController::class, 'installmentDetailPrint']);
    Route::get('/order/purchasePlan/{uuidOrder}/print', [OrderController::class, 'purchasePlanPrint']);
    Route::get('/order/item/{uuidOrder}/print', [OrderController::class, 'logisticPrint']);
});
