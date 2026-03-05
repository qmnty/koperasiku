<?php

namespace App\Enums;

enum AnggotaEnum: string
{
    // Menggunakan nilai string untuk database, dan memberikan konstanta kode
    case ANGGOTA = 'anggota';

    // Tambahkan method untuk mengambil label
    public function label(): string
    {
        return match($this) {
            self::ANGGOTA => 'Anggota',
        };
    }

    // Tambahkan method untuk mengambil prefix kode transaksi
    public function prefix(): string
    {
        return match($this) {
            self::ANGGOTA => 'ANGG',
        };
    }
}