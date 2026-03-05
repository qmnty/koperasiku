<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'anggotas';

    // Daftar field yang boleh diisi secara massal
    protected $fillable = [
        'no_anggota',
        'nama_lengkap',
        'alamat',
        'pj', // Penanggung Jawab / Kelompok
        'no_telepon',
        'tanggal_daftar',
        'saldo_pokok',
        'saldo_wajib',
        'saldo_khusus',
        'saldo_sukarela',
        'plafon_pengajuan',
    ];

    // Cast data untuk tipe data tertentu
    protected $casts = [
        'tanggal_daftar' => 'date',
        'saldo_pokok'    => 'decimal:2',
        'saldo_wajib'    => 'decimal:2',
        'plafon_pengajuan' => 'decimal:2',
    ];

    /**
     * Relasi ke Transaksi: Satu anggota punya banyak transaksi
     */
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    /**
     * Relasi ke Pinjaman: Satu anggota punya banyak kontrak pinjaman
     */
    public function pinjamans()
    {
        return $this->hasMany(Pinjaman::class);
    }
}