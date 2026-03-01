<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pinjamans', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel anggota
            $table->foreignId('anggota_id')
                  ->constrained('anggotas')
                  ->onDelete('cascade');

            $table->string('no_kontrak')->unique();
            $table->date('tanggal_cair');
            
            // Detail Pinjaman (Sesuai form Vue Anda)
            $table->decimal('nominal_realisasi', 15, 2); // nominalCair
            $table->integer('tenor'); // Dalam bulan
            
            // Kalkulasi
            $table->decimal('total_bunga', 15, 2)->default(0);
            $table->decimal('total_tagihan', 15, 2); // Pokok + Bunga
            $table->decimal('angsuran_per_bulan', 15, 2); // total_tagihan / tenor
            
            // Status
            $table->string('status')->default('aktif');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjamans');
    }
};