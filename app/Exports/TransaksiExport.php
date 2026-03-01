<?php
namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransaksiExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = DB::table('transaksis')
            ->join('anggotas', 'transaksis.anggota_id', '=', 'anggotas.id')
            ->select(
                'anggotas.nama_lengkap as nama',
                'anggotas.pj',
                'transaksis.kode_transaksi',
                'transaksis.jenis_transaksi',
                'transaksis.debit',
                'transaksis.kredit',
                'transaksis.keterangan',
                'transaksis.created_at'
            );

        // Terapkan Filter yang sama dengan Fungsi Index
        if (!empty($this->filters['search'])) {
            $query->where('anggotas.nama_lengkap', 'LIKE', '%' . $this->filters['search'] . '%');
        }
        if (!empty($this->filters['kategori'])) {
            $query->where('transaksis.jenis_transaksi', $this->filters['kategori']);
        }
        if (!empty($this->filters['kelompok'])) {
            $query->where('anggotas.pj', $this->filters['kelompok']);
        }
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('transaksis.created_at', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('transaksis.created_at', '<=', $this->filters['end_date']);
        }

        return $query->orderBy('transaksis.created_at', 'desc');
    }

    public function headings(): array
    {
        return ['Nama Anggota', 'Kelompok (PJ)', "ID Transaksi", 'Kategori', 'Masuk (Debit)', 'Keluar (Kredit)', 'Keterangan', 'Tanggal'];
    }

    public function map($t): array
    {
        return [
            $t->nama,
            $t->pj,
            $t->kode_transaksi,
            ucfirst($t->jenis_transaksi),
            $t->debit,
            $t->kredit,
            $t->keterangan,
            date('d/m/Y H:i', strtotime($t->created_at)),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Mengambil nomor baris terakhir yang ada datanya (misal: 150)
        $lastRow = $sheet->getHighestRow();
        // Mengambil huruf kolom terakhir (misal: H)
        $lastColumn = $sheet->getHighestColumn();

        $fullRange = "A1:{$lastColumn}{$lastRow}";

        return [
            // Baris 1 (Header) tetap Bold
            1 => ['font' => ['bold' => true]],
            
            // Border mengikuti rentang data yang ada
            $fullRange => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}