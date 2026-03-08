<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pinjamans', function (Blueprint $blueprint) {
            // Mengubah kolom anggota_id menjadi nullable
            $blueprint->foreignId('anggota_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pinjamans', function (Blueprint $blueprint) {
            // Mengembalikan kolom menjadi tidak nullable (wajib ada)
            $blueprint->foreignId('anggota_id')->nullable(false)->change();
        });
    }
};
