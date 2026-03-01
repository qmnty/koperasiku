<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pinjamans', function (Blueprint $table) {
            // Menambahkan kolom total_bayar
            // Menggunakan decimal agar presisi untuk uang, default 0
            $table->decimal('total_bayar', 15, 2)->default(0)->after('nominal_realisasi');
            
            // Jika kamu juga butuh kolom status lunas di sini
            // $table->enum('status', ['aktif', 'lunas'])->default('aktif')->after('total_bayar');
        });
    }

    public function down(): void
    {
        Schema::table('pinjamans', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn('total_bayar');
        });
    }
};