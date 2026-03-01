<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::post('/login', [AuthController::class, 'login'])->name('login');

//Anggota
Route::prefix('anggota')->group(function () {
    Route::get('/', [AnggotaController::class, 'index'])->name('anggota.index');
    Route::post('/store', [AnggotaController::class, 'store'])->name('anggota.store');
    Route::get('/riwayat-transaksi/{anggotaId}', [AnggotaController::class, 'showRiwayatTransaksi'])->name('anggota.riwayat-transaksi');
    Route::post('/simpanan/store', [AnggotaController::class, 'storeSimpanan'])->name('anggota.simpanan.store');
    Route::post('/simpanan/tarik-store', [AnggotaController::class, 'storeTarik'])->name('anggota.simpanan.tarik');
    Route::post('/pinjaman/store', [AnggotaController::class, 'storePinjaman'])->name('anggota.pinjaman.store');
});

Route::prefix('pinjaman')->group(function () {
    Route::get('/', [PinjamanController::class, 'index'])->name('pinjaman.index');
    Route::get('/detail/{id}', [PinjamanController::class, 'detail'])->name('pinjaman.detail');
    Route::post('/bayar', [PinjamanController::class, 'bayar'])->name('pinjaman.bayar');
    Route::get('/riwayat/{id}', [PinjamanController::class, "history"])->name('pinjaman.history');
});

Route::prefix('transaksi')->group(function () {
    Route::get('/', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/riwayat/{anggotaId}', [AnggotaController::class, 'showRiwayatTransaksi'])->name('anggota.riwayat-transaksi');
    Route::get('export', [TransaksiController::class, 'exportExcel'])->name('transaksi.export');
});

Route::get('date/{tenor}', function($tenor) {   
    $tanggalCair = Carbon::parse(Carbon::now())->setTimezone('Asia/Jakarta');             
    return $tanggalCair->copy()->addMonths((int) $tenor);
})->where('tenor', '[0-9]+'); // Hanya menerima angka 0-9

Route::get('users', [UserController::class, 'index']);
Route::post('users', [UserController::class, 'store']);
Route::delete('users/{id}', [UserController::class, 'destroy']);