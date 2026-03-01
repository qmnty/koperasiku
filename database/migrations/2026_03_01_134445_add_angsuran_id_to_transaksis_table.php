<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            // Menambahkan kolom angsuran_id (unsignedBigInteger)
            // after('id') opsional, agar posisi kolom rapi setelah ID
            $table->unsignedBigInteger('angsuran_id')->nullable()->after('id');

            // Opsional: Jika ingin membuat relasi ke tabel pinjamans/angsurans
            // $table->foreign('angsuran_id')->references('id')->on('pinjamans')->onDelete('cascade');
            
            // Tambahkan index agar pencarian riwayat (Query Builder) lebih cepat
            $table->index('angsuran_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropIndex(['angsuran_id']); // Drop index dulu
            $table->dropColumn('angsuran_id');
        });
    }
};