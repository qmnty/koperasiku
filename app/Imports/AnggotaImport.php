<?php
namespace App\Imports;

use App\Enums\TransaksiEnum;
use App\Models\Anggota;
use App\Enums\AnggotaEnum;
use App\Models\Transaksi;
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
        $nama = trim($row['nama_anggota']);

        if (empty($nama) || $nama == 'TOTAL') {
            return null;
        }

        $tanggal = $this->transformDate($row['tanggal']);
        
        // --- LOGIC NOMOR ANGGOTA ---
        // $tahun = Carbon::parse($tanggal)->format('y');
        // $lastAnggota = Anggota::whereYear('created_at', Carbon::parse($tanggal)->year)
        //                 ->orderBy('id', 'desc')
        //                 ->first();
        
        // $lastSequence = $lastAnggota ? (int) substr($lastAnggota->no_anggota, -3) : 0;
        $noAnggota = AnggotaEnum::ANGGOTA->prefix() . $row['nia'];

        // 1. Simpan Anggota Terlebih Dahulu
        $anggota = Anggota::create([
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

        // 2. Buat Transaksi untuk masing-masing saldo (jika ada isinya)
        $jenisSimpanan = [
            ['key' => 'pokok', 'enum' => TransaksiEnum::POKOK, 'ket' => 'Simpanan Pokok'],
            ['key' => 'wajib', 'enum' => TransaksiEnum::WAJIB, 'ket' => 'Simpanan Wajib'],
            ['key' => 'sukarela', 'enum' => TransaksiEnum::SUKARELA, 'ket' => 'Simpanan Sukarela'],
        ];

        foreach ($jenisSimpanan as $index => $simpanan) {
            $nominal = $this->cleanNumber($row[$simpanan['key']]);
            
            if ($nominal > 0) {
                // Gabungkan: Prefix + Timestamp + Microseconds + Index Loop
                $uniqueCode = $simpanan['enum']->prefix() . "-" . 
                              now()->format('YmdHisu') . 
                              $index . 
                              $anggota->id;

                Transaksi::create([
                    'anggota_id'        => $anggota->id,
                    'kode_transaksi'    => $uniqueCode,
                    'tanggal_transaksi' => $tanggal,
                    'jenis_transaksi'   => $simpanan['enum'],
                    'debit'             => $nominal,
                    'kredit'            => 0,
                    'keterangan'        => 'Setoran Awal ' . $simpanan['ket'],
                ]);
            }
        }

        return $anggota;
    }

    public function headingRow(): int
    {
        return 1;
    }

    private function cleanNumber($value)
    {
        if (empty($value)) return 0;
        
        // 1. Hapus "Rp" atau karakter non-digit kecuali titik/koma
        $clean = preg_replace('/[^0-9,.]/', '', $value);
        
        // 2. Jika formatnya 1.000,00 (standar Indo), ubah titik jadi kosong, koma jadi titik
        if (strpos($clean, ',') !== false && strpos($clean, '.') !== false) {
            $clean = str_replace('.', '', $clean); // Buang titik ribuan
            $clean = str_replace(',', '.', $clean); // Ubah koma desimal ke titik
        } 
        // 3. Jika hanya ada titik (misal 1.000)
        elseif (strpos($clean, '.') !== false && strpos($clean, ',') === false) {
            // Hati-hati: apakah ini ribuan atau desimal? 
            // Biasanya di Excel tanpa koma, titik adalah ribuan.
            $clean = str_replace('.', '', $clean);
        }
        // 4. Jika hanya ada koma (misal 1000,50)
        elseif (strpos($clean, ',') !== false) {
            $clean = str_replace(',', '.', $clean);
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