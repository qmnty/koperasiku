<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            // Menambahkan kolom payment_method setelah kolom total_bayar (contoh)
            // Kamu bisa ganti ->after('nama_kolom') sesuai urutan yang diinginkan
            $table->string('payment_method', 50)->nullable()->default('cash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};