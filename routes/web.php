<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

Route::get('/', [FileUploadController::class, 'index'])->name('file.upload.index');
Route::post('/file/store', [FileUploadController::class, 'store'])->name('file.upload.store');
Route::get('/file/list', [FileUploadController::class, 'list'])->name('file.upload.list');
