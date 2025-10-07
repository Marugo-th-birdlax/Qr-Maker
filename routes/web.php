<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportsController;

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt'); // << สำคัญ
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/', [HomeController::class, 'index'])->name('home');


// (มีของเดิมสำหรับดาวน์โหลดไฟล์)
Route::post('/qr/download', [QrController::class,'download'])->name('qr.download');
Route::post('qr/export-pdf', [QrController::class, 'exportPdf'])->name('qr.export.pdf');

Route::get('/qr/print/single', [QrController::class,'printSingle'])->name('qr.print.single'); // ?part_id&copies=8
Route::post('/qr/print/bulk',  [QrController::class,'printBulk'])->name('qr.print.bulk');     // ids[] + copies_per_item
Route::get('parts/qr/bulk',  [QrController::class,'bulkView'])->name('parts.qr.bulk.view');


Route::prefix('reports')->group(function () {
    Route::get('/',            [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/export/xlsx', [ReportsController::class, 'exportXlsx'])->name('reports.export.xlsx');
});

Route::prefix('parts')->name('parts.')->group(function () {
    Route::get('/', [PartController::class,'index'])->name('index');

     // ── QR
    Route::post('qr/bulk', [App\Http\Controllers\QrController::class,'bulkFromIds'])->name('qr.bulk');
    Route::get('{part}/qr', [App\Http\Controllers\QrController::class,'showForPart'])->name('qr.show');

    // ── Edit/Update: admin, manager
    Route::middleware(['auth.session','role:admin,pp,qc'])->group(function () {
        Route::get('{part}/edit',       [PartController::class,'edit'])->name('edit')->whereNumber('part');
        Route::put('{part}',            [PartController::class,'update'])->name('update')->whereNumber('part');
        Route::patch('{part}/activate', [PartController::class,'activate'])->name('activate')->whereNumber('part');
        Route::patch('{part}/deactivate',[PartController::class,'deactivate'])->name('deactivate')->whereNumber('part');
        Route::get('{part}/history',    [PartController::class,'history'])->name('history')->whereNumber('part');
    });

   // ── Create/Import: admin
    Route::middleware(['auth.session','role:admin,pp'])->group(function () {
        Route::get('import', [PartController::class,'createImport'])->name('import.form');
        Route::post('import', [PartController::class,'storeImport'])->name('import.store');

        // ✅ แก้ตรงนี้ ไม่ต้องมี /parts ซ้ำ
        Route::get('create', [PartController::class, 'create'])->name('create');
        Route::post('/',     [PartController::class, 'store'])->name('store');

    });

        // ── Delete: pc only
    Route::middleware(['auth.session','role:admin,pc'])->group(function () {
        Route::get('{part}/delete', [PartController::class,'deleteConfirm'])
            ->name('delete.confirm')->whereNumber('part');
        Route::delete('{part}', [PartController::class,'destroy'])
            ->name('destroy')->whereNumber('part');
    });
});

Route::prefix('settings')->name('settings.')->middleware(['auth.session','role:admin,pc'])->group(function () {
    Route::get('/',                   [PartController::class,'settings'])->name('index');
    Route::patch('trash/{id}/restore',[PartController::class,'restore'])->name('restore')->whereNumber('id');
    Route::delete('trash/{id}/force', [PartController::class,'forceDelete'])->name('force')->whereNumber('id');
});

