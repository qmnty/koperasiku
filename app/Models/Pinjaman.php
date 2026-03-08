<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pinjaman extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'pinjamans';

    // Daftar field yang boleh diisi secara massal
    protected $fillable = [
        'anggota_id',
        'no_kontrak',
        'tanggal_cair',
        'nominal_plafon', // Tambahan plafon kontrak
        'nominal_realisasi',
        'tenor',
        'total_bunga',
        'total_bayar',
        'total_tagihan',
        'angsuran_per_bulan',
        'status',
        'jatuh_tempo',
        'sisa_tenor',
    ];

    // Cast data untuk tipe data tertentu
    protected $casts = [
        'tanggal_cair'      => 'date',
        'nominal_plafon'    => 'decimal:2',
        'nominal_realisasi' => 'decimal:2',
        'total_bunga'       => 'decimal:2',
        'total_tagihan'     => 'decimal:2',
        'angsuran_per_bulan' => 'decimal:2',
    ];

    /**
     * Relasi ke Anggota: Pinjaman dimiliki oleh satu anggota
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    /**
     * Scope untuk memfilter pinjaman berdasarkan status (lancar, macet, lunas)
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}