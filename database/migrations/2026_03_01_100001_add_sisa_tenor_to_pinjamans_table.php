<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pinjamans', function (Blueprint $table) {
            // Tambahkan kolom sisa_tenor dengan tipe integer
            // Default ke 0 atau samakan dengan tenor saat pembuatan pertama kali
            $table->integer('sisa_tenor')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('pinjamans', function (Blueprint $table) {
            $table->dropColumn('sisa_tenor');
        });
    }
};