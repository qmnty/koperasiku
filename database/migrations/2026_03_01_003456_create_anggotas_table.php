<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggotas', function (Blueprint $table) {
            $table->id();
            // Sesuaikan dengan nama kolom yang ada di sheet "Anggota"
            $table->string('no_anggota')->unique(); // ID Anggota (biasanya otomatis/input)
            $table->string('nama_lengkap');
            $table->string('alamat')->nullable();
            $table->string('pj')->nullable(); // Penanggung Jawab / Kelompok
            $table->string('no_telepon')->nullable();
            $table->date('tanggal_daftar')->nullable();
            
            // Kolom keuangan awal (jika perlu tercatat di tabel anggota)
            $table->decimal('saldo_pokok', 15, 2)->default(0);
            $table->decimal('saldo_wajib', 15, 2)->default(0);
            $table->decimal('saldo_khusus', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggotas');
    }
};