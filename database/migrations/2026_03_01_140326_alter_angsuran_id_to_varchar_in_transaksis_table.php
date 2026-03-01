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
            // Mengubah tipe kolom angsuran_id menjadi string (varchar)
            // Anda bisa menentukan panjangnya, misal 50 karakter
            $table->string('angsuran_id', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            // Mengembalikan ke tipe data awal (misal: bigInteger) jika di-rollback
            $table->unsignedBigInteger('angsuran_id')->nullable()->change();
        });
    }
};