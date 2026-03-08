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

class PinjamanImport implements ToModel, WithHeadingRow
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

        if (empty($no_kontrak) || $no_kontrak == 'TOTAL' || Pinjaman::where('no_kontrak', $no_kontrak)->exists()) {
            return null;
        }

        $tanggal_cair = $this->transformDate($row['tanggal_cair']);
        $jatuh_tempo = $this->transformDate($row['jatuh_tempo']);
        
        // $lastSequence = $lastAnggota ? (int) substr($lastAnggota->no_anggota, -3) : 0;
        $noAnggota = AnggotaEnum::ANGGOTA->prefix().$row['nia'];
        $idAnggota = DB::table('anggotas')->where('no_anggota', $noAnggota)->select('id')->first();

        // 1. Simpan Anggota Terlebih Dahulu
        $pinjaman = Pinjaman::create([
            'no_kontrak'     => TransaksiEnum::PINJAMAN->prefix() . Str::after($no_kontrak, 'LOAN'),
            'anggota_id'     => $idAnggota->id ?? null,
            'tanggal_cair'   => $tanggal_cair,
            'nominal_realisasi' => $this->cleanNumber($row['realisasi']),
            'tenor'          => $row['tenor'],
            'total_bunga'    => $this->cleanNumber($row['realisasi']) * ($row['tenor'] * 0.01),
            'total_tagihan'  => $this->cleanNumber($row['realisasi']) + ( $this->cleanNumber($row['realisasi']) * ($row['tenor'] * 0.01)),
            'angsuran_per_bulan' => $this->cleanNumber($row['angsur']),
            'jatuh_tempo'    => $jatuh_tempo,
            'sisa_tenor'     => $row['tenor'] - $row['angsurke'],
            'total_bayar'    => $this->cleanNumber($row['angsur']) * $row['angsurke']
        ]);

        if ($row['angsurke'] > 0) {
            foreach (range(1, $row['angsurke']) as $ke) {
                $nominalPerBulan = $this->cleanNumber($row['angsur']);
                $uniqueCode = TransaksiEnum::ANGSURAN->prefix() . "-" . 
                            now()->format('YmdHisv') .
                            $ke . 
                            ($idAnggota->id ?? '0');

                Transaksi::create([
                    'anggota_id'        => $idAnggota->id ?? -1,
                    'pinjaman_id'       => $pinjaman->id, 
                    'kode_transaksi'    => $uniqueCode,
                    'tanggal_transaksi' => $tanggal_cair, // Bisa disesuaikan
                    'jenis_transaksi'   => TransaksiEnum::ANGSURAN->label(),
                    'debit'             => $nominalPerBulan, 
                    'kredit'            => 0,
                    'keterangan'        => "Pembayaran Angsuran ke-{$ke} (Import Data)",
                ]);
            }
        }

        return $pinjaman;
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