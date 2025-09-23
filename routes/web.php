<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\QrController;


Route::get('/', [HomeController::class, 'index'])->name('home');


Route::prefix('parts')->name('parts.')->group(function () {
    Route::get('/',            [PartController::class,'index'])->name('index');
    Route::get('/import',      [PartController::class,'createImport'])->name('import.form');  // << ใช้ชื่อนี้
    Route::post('/import',     [PartController::class,'storeImport'])->name('import.store');
    Route::post('/qr/bulk',    [App\Http\Controllers\QrController::class,'bulkFromIds'])->name('qr.bulk');
    Route::get('{part}/qr',    [App\Http\Controllers\QrController::class,'showForPart'])->name('qr.show');
});

// (มีของเดิมสำหรับดาวน์โหลดไฟล์)
Route::post('/qr/download', [QrController::class,'download'])->name('qr.download');
Route::post('qr/export-pdf', [QrController::class, 'exportPdf'])->name('qr.export.pdf');


Route::get('/qr/print/single', [QrController::class,'printSingle'])->name('qr.print.single'); // ?part_id&copies=8
Route::post('/qr/print/bulk',  [QrController::class,'printBulk'])->name('qr.print.bulk');     // ids[] + copies_per_item
Route::get('parts/qr/bulk',  [QrController::class,'bulkView'])->name('parts.qr.bulk.view');