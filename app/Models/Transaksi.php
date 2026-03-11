<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    // Menentukan tabel jika tidak menggunakan nama jamak default (transaksis)
    protected $table = 'transaksis';

    // Daftar field yang boleh diisi secara massal
    protected $fillable = [
        'anggota_id',
        'kode_transaksi',
        'tanggal_transaksi',
        'jenis_transaksi',
        'debit',
        'kredit',
        'angsuran_ke',
        'tenor_total',
        'keterangan',
        'angsuran_id',
        'payment_method'
    ];

    // Cast data tipe data agar otomatis berubah tipe
    protected $casts = [
        'tanggal_transaksi' => 'date',
        'debit' => 'decimal:2',
        'kredit' => 'decimal:2',
    ];

    // Konstanta untuk jenis transaksi agar tidak typo
    const TYPE_ANGSURAN = 'angsuran';
    const TYPE_SUKARELA = 'sukarela';
    const TYPE_PENCAIRAN = 'pencairan';

    /**
     * Relasi ke Anggota: Satu transaksi milik satu anggota
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    /**
     * Scope untuk memfilter transaksi berdasarkan jenis
     */
    public function scopeJenis($query, $type)
    {
        return $query->where('jenis_transaksi', $type);
    }
}
