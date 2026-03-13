<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
            // Menambahkan kolom status, default 'aktif'
            // after('nama') agar posisi kolom rapi di database (opsional)
            $table->string('status', 20)->default('aktif');
            
            // Tambahkan index agar pencarian status (filter) lebih cepat
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};