<?php

namespace App\Http\Controllers;

use App\Enums\TransaksiEnum;
use App\Exports\TransaksiExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('transaksis')
                ->join('anggotas', 'transaksis.anggota_id', '=', 'anggotas.id')
                ->select(
                    'transaksis.id',
                    'anggotas.nama_lengkap as nama',
                    'anggotas.pj as kelompok', // Pastikan ambil data kelompok
                    'transaksis.jenis_transaksi as tipe',
                    'transaksis.kredit',
                    'transaksis.debit',
                    'transaksis.keterangan as notes',
                    'transaksis.tanggal_transaksi',
                    DB::raw('LOWER(transaksis.payment_method) as payment_method')
                );

            if ($request->filled('search')) {
                $query->where('anggotas.nama_lengkap', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->filled('start_date')) {
                $query->whereDate('transaksis.tanggal_transaksi', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('transaksis.tanggal_transaksi', '<=', $request->end_date);
            }

            if ($request->filled('kategori')) {
                if($request->kategori == 'setor') {
                    $query->whereIn('transaksis.jenis_transaksi', [
                        TransaksiEnum::SUKARELA->value,
                        TransaksiEnum::POKOK->value,
                        TransaksiEnum::WAJIB->value,
                        TransaksiEnum::TARIK->value,
                        TransaksiEnum::PENCAIRAN->value,
                        TransaksiEnum::ANGSURAN->value
                    ]);
                } else {
                    $query->where('transaksis.jenis_transaksi', $request->kategori);
                }
            }

            // Filter Kelompok Baru
            if ($request->filled('kelompok')) {
                $query->where('anggotas.pj', $request->kelompok);
            }

            $paginatedData = $query->orderBy('transaksis.tanggal_transaksi', 'desc')
                ->paginate($request->query('per_page', 15));

            $paginatedData->setCollection($paginatedData->getCollection()->map(function($t) {
                $isKeluar = $t->kredit > 0;
                return [
                    'id' => $t->id,
                    'nama' => $t->nama,
                    'kelompok' => $t->kelompok,
                    'tipe' => $t->tipe,
                    'is_keluar' => $isKeluar,
                    'nominal' => $isKeluar ? $t->kredit : $t->debit,
                    'tanggal' => date('d M Y, H:i', strtotime($t->tanggal_transaksi)),
                    'notes' => $t->notes,
                    'payment_method' => $t->payment_method
                ];
            }));

            return response()->json($paginatedData, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function exportExcel(Request $request) 
    {
        $filters = $request->all();
        $fileName = 'Laporan_Transaksi_' . date('Ymd_His') . '.xlsx';
        
        return Excel::download(new TransaksiExport($filters), $fileName);
    }

    public function kategori()
    {
        $options = collect(TransaksiEnum::cases())->map(function($case) {
            return [
                'value' => $case->value,
                'label' => $case->value
            ];
        });
        
        return response()->json($options);
    }
}
