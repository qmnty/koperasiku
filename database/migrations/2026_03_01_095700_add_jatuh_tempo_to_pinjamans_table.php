<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pinjamans', function (Blueprint $table) {
            // Tambahkan nilai default, contoh: default ke waktu sekarang
            // agar SQLite tidak error untuk data lama
            $table->date('jatuh_tempo')->default(DB::raw('CURRENT_DATE'))->after('tanggal_cair');
        });
    }

    public function down(): void
    {
        Schema::table('pinjamans', function (Blueprint $table) {
            $table->dropColumn('jatuh_tempo');
        });
    }
};