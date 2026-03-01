<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Anggota
            $table->foreignId('anggota_id')->constrained('anggotas')->onDelete('cascade');

            // Header Transaksi
            $table->string('kode_transaksi')->unique(); // Contoh: TRX-20240001
            $table->date('tanggal_transaksi');
            
            // Pembeda Jenis Transaksi
            // angsuran: bayar cicilan pinjaman
            // sukarela: tabungan yang bisa diambil kapan saja
            // pencairan: uang keluar saat pinjaman disetujui
            $table->enum('jenis_transaksi', ['angsuran', 'sukarela', 'pencairan', 'wajib', 'khusus', 'pokok', 'tarik']);

            // Nominal Keuangan
            $table->decimal('debit', 15, 2)->default(0);  // Uang masuk (Angsuran/Sukarela)
            $table->decimal('kredit', 15, 2)->default(0); // Uang keluar (Pencairan)
            
            // Detail Tambahan (Nullable karena tidak semua jenis transaksi butuh ini)
            $table->integer('angsuran_ke')->nullable(); // Khusus jenis 'angsuran'
            $table->integer('tenor_total')->nullable(); // Referensi tenor saat pencairan
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};