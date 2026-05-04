<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- استيراد الـ Controllers الخاصة بالنظام الجديد ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\LotteryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- المسارات العامة (Public Routes) ---
Route::post('/login', [AuthController::class, 'login']);

// --- المسارات المحمية (Protected Routes) ---
Route::middleware('auth:sanctum')->group(function () {


    // 2. إدارة النسخ الاحتياطي
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->middleware('can:backup.view');
        Route::post('/', [BackupController::class, 'store'])->middleware('can:backup.create');
        Route::get('/download', [BackupController::class, 'download'])->middleware('can:backup.download');
        Route::delete('/', [BackupController::class, 'destroy'])->middleware('can:backup.delete');
    });

    // 3. إدارة المستخدمين والأدوار
    Route::apiResource('users', UserController::class);
    Route::get('roles/permissions', [RoleController::class, 'getAllPermissions'])->name('roles.permissions');
    Route::apiResource('roles', RoleController::class);

    // 4. إدارة العملاء (النزلاء)
    Route::apiResource('clients', ClientController::class);

    // 5. إدارة سحوبات القرعة
    // نحتاج فقط للعرض (index)، إنشاء سحب أوتوماتيكي (store)، وإلغاء/حذف سحب (destroy)
    Route::apiResource('lottery-draws', LotteryController::class)->only(['index', 'store', 'destroy']);

    // 6. بيانات المستخدم الحالي وتسجيل الخروج
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load('roles:id,name', 'roles.permissions:id,name');
        return response()->json($user);
    });
    Route::post('/logout', [AuthController::class, 'logout']);

});
