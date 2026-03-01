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
        Schema::table('anggotas', function (Blueprint $table) {
            // Menambahkan kolom saldo_sukarela dengan tipe decimal (untuk presisi uang)
            // default 0 agar tidak null saat pertama dibuat
            $table->decimal('saldo_sukarela', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn('saldo_sukarela');
        });
    }
};