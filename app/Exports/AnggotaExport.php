<?php
namespace App\Exports;

use App\Models\Anggota;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat; // Tambahkan ini

class AnggotaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    private $rowNumber = 0;

    public function collection()
    {
        return Anggota::all();
    }

    public function headings(): array
    {
        return [
            'NO', 'TANGGAL', 'NAMA ANGGOTA', 'NIA', 'PJ', 'POKOK', 'WAJIB', 'KHUSUS', 'SUKARELA', 'TOTAL'
        ];
    }

    public function map($anggota): array
    {
        $this->rowNumber++;
        
        // Pastikan input ke Excel adalah angka (float/int) agar format currency bekerja
        $pokok = (float) $anggota->saldo_pokok;
        $wajib = (float) $anggota->saldo_wajib;
        $khusus = (float) $anggota->saldo_khusus;
        $sukarela = (float) $anggota->saldo_sukarela;
        $total = $pokok + $wajib + $khusus + $sukarela;

        return [
            $this->rowNumber,
            $anggota->created_at->format('d/m/Y'),
            $anggota->nama_lengkap,
            $anggota->no_anggota,
            $anggota->pj,
            $pokok,
            $wajib,
            $khusus,
            $sukarela,
            $total
        ];
    }

    /**
     * Format Kolom F sampai J (Pokok s/d Total) menjadi Rupiah
     */
    public function columnFormats(): array
    {
        return [
            'F' => '"Rp "#,##0',
            'G' => '"Rp "#,##0',
            'H' => '"Rp "#,##0',
            'I' => '"Rp "#,##0',
            'J' => '"Rp "#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->rowNumber + 1;
        $lastColumn = 'J';

        return [
            // Styling Header (Baris 1)
            1 => [
                'font' => ['bold' => true, 'size' => 18],
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