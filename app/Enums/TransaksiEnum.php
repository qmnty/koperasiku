<?php

namespace App\Enums;

enum TransaksiEnum: string
{
    // Menggunakan nilai string untuk database, dan memberikan konstanta kode
    case ANGSURAN = 'angsuran';
    case SUKARELA = 'sukarela';
    case PENCAIRAN = 'pencairan';
    case POKOK = 'pokok';
    case WAJIB = 'wajib';
    case TARIK = 'tarik';
    case PINJAMAN = 'pinjaman';

    // Tambahkan method untuk mengambil label
    public function label(): string
    {
        return match($this) {
            self::ANGSURAN => 'Angsuran Pinjaman',
            self::SUKARELA => 'Simpanan Sukarela/Pokok',
            self::PENCAIRAN => 'Pencairan Dana Pinjaman',
            self::POKOK => 'Simpanan Pokok',
            self::WAJIB => 'Simpanan Wajib',
            self::TARIK => 'Tarik Tunai',
            self::PINJAMAN => 'Pinjaman'
        };
    }

    // Tambahkan method untuk mengambil prefix kode transaksi
    public function prefix(): string
    {
        return match($this) {
            self::ANGSURAN => 'ANGSUR',
            self::SUKARELA => 'SUKARELA',
            self::PENCAIRAN => 'CAIR',
            self::POKOK => 'POKOK',
            self::WAJIB => 'WAJIB',
            self::TARIK => 'TARIK',
            self::PINJAMAN => 'LOAN'
        };
    }
}