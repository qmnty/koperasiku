<?php
namespace App\Imports;

use App\Models\Anggota;
use App\Enums\AnggotaEnum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class AnggotaImport implements ToModel, WithHeadingRow
{
    private $nextId;

    public function __construct()
    {
        // Ambil ID terakhir sekali saja saat class diinstansiasi
        $lastMember = Anggota::orderBy('id', 'desc')->first();
        $this->nextId = $lastMember ? $lastMember->id + 1 : 1;
    }

    public function model(array $row)
    {
        // 1. Bersihkan data nama
        $nama = trim($row['nama_anggota']);

        // 2. Skip jika baris kosong atau baris TOTAL
        if (empty($nama) || $nama == 'TOTAL') {
            return null;
        }

        // 3. DETEKSI: Cek apakah nama sudah ada di database
        // Kita gunakan where nama_lengkap untuk cek duplikat
        $isExist = Anggota::where('nama_lengkap', $nama)->exists();

        if ($isExist) {
            // Jika nama sudah ada, baris ini dilewati (skip)
            return null;
        }

        // 4. Proses data jika belum ada
        $tanggal = $this->transformDate($row['tanggal']);
        
        // Generate No Anggota menggunakan counter memori ($this->nextId)
        $noAnggota = AnggotaEnum::ANGGOTA->prefix() . "-" .
                     Carbon::now()->format('Ymd') .
                     str_pad($this->nextId, 4, '0', STR_PAD_LEFT);

        // Naikkan counter untuk baris berikutnya di excel yang sama
        $this->nextId++;

        return new Anggota([
            'nama_lengkap'   => $nama,
            'pj'             => $row['pj'],
            'saldo_pokok'    => $this->cleanNumber($row['pokok']),
            'saldo_wajib'    => $this->cleanNumber($row['wajib']),
            'saldo_khusus'   => $this->cleanNumber($row['khusus']),
            'saldo_sukarela' => $this->cleanNumber($row['sukarela']),
            'created_at'     => $tanggal,
            'updated_at'     => $tanggal,
            'no_anggota'     => $noAnggota
        ]);
    }

    public function headingRow(): int
    {
        return 3;
    }

    private function cleanNumber($value)
    {
        return $value ? (int) preg_replace('/[^0-9]/', '', $value) : 0;
    }

    private function transformDate($value)
    {
        if (empty($value)) return now();
        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return now();
        }
    }
}