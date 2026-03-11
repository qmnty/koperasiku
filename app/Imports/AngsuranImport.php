<?php
namespace App\Imports;

use App\Enums\TransaksiEnum;
use App\Enums\AnggotaEnum;
use App\Models\Pinjaman;
use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AngsuranImport implements ToModel, WithHeadingRow
{
    private $nextId;

    public function __construct()
    {
        // Ambil ID terakhir sekali saja saat class diinstansiasi
        $lastMember = Pinjaman::orderBy('id', 'desc')->first();
        $this->nextId = $lastMember ? $lastMember->id + 1 : 1;
    }

    public function model(array $row)
    {
        $no_kontrak = trim($row['id_pinjam']);

        if (empty($no_kontrak) || $no_kontrak == 'TOTAL' || !Pinjaman::where('no_kontrak', $no_kontrak)->exists()) {
            return null;
        }

        $tanggal = $this->transformDate($row['tanggal']);
        
        // $lastSequence = $lastAnggota ? (int) substr($lastAnggota->no_anggota, -3) : 0;
        $noAnggota = AnggotaEnum::ANGGOTA->prefix().$row['nia'];
        $idAnggota = DB::table('anggotas')->where('no_anggota', $noAnggota)->select('id')->first();

        $idAngsur = Str::after($row['id_angsur'], 'ANGSUR');

        $angsuran = Transaksi::create([
            'angsuran_id' => $no_kontrak,
            'anggota_id' => $idAnggota->id,
            'kode_transaksi' => TransaksiEnum::ANGSURAN->prefix().$idAngsur,
            'debit' => $this->cleanNumber($row['total']), //Angsuran pokok + bunga
            'kredit' => 0,
            'tanggal_transaksi' => $tanggal,
            'jenis_transaksi' => TransaksiEnum::ANGSURAN->value,
            'payment_method' => $row['payment'],
            'keterangan' => "Pembayaran Angsuran Pinjaman ".TransaksiEnum::PINJAMAN->prefix()."{$idAngsur} (Import Data)"
        ]);

        // // 1. Simpan Anggota Terlebih Dahulu

        // $angsuranid = TransaksiEnum::PINJAMAN->prefix() . Str::after($no_kontrak, 'LOAN');
        // $totalBayar = ( $this->cleanNumber($row['angsur']) + ($this->cleanNumber($row['realisasi']*0.01)) );
        return $angsuran;
    }

    public function headingRow(): int
    {
        return 1;
    }

    private function cleanNumber($value)
    {
        // Jika sudah berupa angka (integer/float), kembalikan langsung
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (empty($value)) return 0;

        // 1. Bersihkan karakter non-digit selain koma dan titik
        $clean = preg_replace('/[^0-9,.]/', '', (string)$value);
        
        // 2. Deteksi pemisah ribuan vs desimal
        // Strategi: Cari posisi terakhir dari titik atau koma
        $lastComma = strrpos($clean, ',');
        $lastDot = strrpos($clean, '.');

        if ($lastComma !== false && $lastDot !== false) {
            // Jika koma muncul setelah titik (format 1.000,50), titik adalah ribuan
            if ($lastComma > $lastDot) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                // Jika titik muncul setelah koma (format 1,000.50), koma adalah ribuan
                $clean = str_replace(',', '', $clean);
            }
        } elseif ($lastComma !== false) {
            // Jika hanya ada koma, tentukan apakah itu ribuan atau desimal
            // Jika panjang string setelah koma adalah 3, asumsikan ribuan (misal: 1,000)
            if (strlen(substr($clean, $lastComma + 1)) == 3) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace(',', '.', $clean);
            }
        } elseif ($lastDot !== false) {
            // Jika hanya ada titik, tentukan apakah itu ribuan atau desimal
            if (strlen(substr($clean, $lastDot + 1)) == 3) {
                $clean = str_replace('.', '', $clean);
            }
        }

        return (float) $clean;
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