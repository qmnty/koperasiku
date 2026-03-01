<?php

namespace App\Http\Controllers;

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
                    'transaksis.created_at'
                );

            if ($request->filled('search')) {
                $query->where('anggotas.nama', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->filled('start_date')) {
                $query->whereDate('transaksis.created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('transaksis.created_at', '<=', $request->end_date);
            }

            if ($request->filled('kategori')) {
                if($request->kategori == 'setor') {
                    $query->where('transaksis.jenis_transaksi', 'sukarela')
                    ->orWhere('transaksis.jenis_transaksi', 'pokok')
                    ->orWhere('transaksis.jenis_transaksi', 'wajib')
                    ->orWhere('transaksis.jenis_transaksi', 'khusus');
                } else {
                    $query->where('transaksis.jenis_transaksi', $request->kategori);
                }
            }

            // Filter Kelompok Baru
            if ($request->filled('kelompok')) {
                $query->where('anggotas.pj', $request->kelompok);
            }

            $data = $query->orderBy('transaksis.created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function($t) {
                    $isKeluar = $t->kredit > 0;
                    return [
                        'id' => $t->id,
                        'nama' => $t->nama,
                        'kelompok' => $t->kelompok,
                        'tipe' => $t->tipe,
                        'is_keluar' => $isKeluar,
                        'nominal' => $isKeluar ? $t->kredit : $t->debit,
                        'tanggal' => date('d M Y, H:i', strtotime($t->created_at)),
                        'notes' => $t->notes
                    ];
                });

            return response()->json($data, 200);
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
}