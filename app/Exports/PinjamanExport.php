<?php
namespace App\Exports;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat; // Tambahkan ini

class PinjamanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    private $rowNumber = 0;

    public function collection()
    {
        return Pinjaman::all();
    }

    public function headings(): array
    {
        return [
            'NO', 'ID PINJAM', 'NIA', 'TANGGAL CAIR', 'REALISASI', 'TENOR', 'ANGSURAN BULANAN', 'BUNGA', 'SISA', 'JATUH TEMPO', 'ANGSURAN', 'SISA ANGSURAN', 'STATUS'
        ];
    }

    public function map($data): array
    {
        $this->rowNumber++;
        
        // Pastikan input ke Excel adalah angka (float/int) agar format currency bekerja
        // $pokok = (float) $anggota->saldo_pokok;
        // $wajib = (float) $anggota->saldo_wajib;
        // $khusus = (float) $anggota->saldo_khusus;
        // $sukarela = (float) $anggota->saldo_sukarela;
        // $total = $pokok + $wajib + $khusus + $sukarela;

        // return [
        //     $this->rowNumber,
        //     $anggota->created_at->format('d/m/Y'),
        //     $anggota->nama_lengkap,
        //     $anggota->no_anggota,
        //     $anggota->pj,
        //     $pokok,
        //     $wajib,
        //     $khusus,
        //     $sukarela,
        //     $total
        // ];
        $no_anggota = DB::table('anggotas')
          ->select('no_anggota')
          ->where('id', $data->anggota_id)
          ->first();

        $totalBayar = ($data->angsuran_per_bulan + ($data->nominal_realisasi * 0.01 ));
        $sisaHutang = $data->total_tagihan - $data->total_bayar;
        
        $sisaTenorManual = ($totalBayar > 0) 
            ? ceil($sisaHutang / $totalBayar) 
            : 0;

        return [
          $this->rowNumber,
          $data->no_kontrak,
          $no_anggota->no_anggota,
          $data->tanggal_cair,
          $data->nominal_realisasi,
          $data->tenor,
          $data->angsuran_per_bulan,
          $data->realisasi * ($data->tenor * 0.01), //bunga
          $sisaTenorManual,
          $data->jatuh_tempo,
          $data->tenor - $sisaTenorManual,
          $data->total_bayar,
          $sisaTenorManual ? 'aktif' : 'lunas'
        ];
    }

    /**
     * Format Kolom F sampai J (Pokok s/d Total) menjadi Rupiah
     */
    public function columnFormats(): array
    {
        return [
            'E' => '"Rp "#,##0',
            'G' => '"Rp "#,##0',
            'H' => '"Rp "#,##0',
            'L' => '"Rp "#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->rowNumber + 1;
        $lastColumn = 'M';

        return [
            // Styling Header (Baris 1)
            1 => [
                'font' => ['bold' => true, 'size' => 15],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D3D3D3'] 
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],

            // Styling Row Data (Baris 2 s/d Selesai)
            "A2:{$lastColumn}{$lastRow}" => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F5F5']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }
}