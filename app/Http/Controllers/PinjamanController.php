<?php

namespace App\Http\Controllers;

use App\Enums\TransaksiEnum;
use App\Exports\PinjamanExport;
use App\Imports\AngsuranImport;
use App\Imports\PinjamanImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PinjamanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Inisialisasi Query Builder (Jangan pakai ->get() di sini)
        $query = DB::table('pinjamans as p')
            ->join('anggotas as a', 'p.anggota_id', '=', 'a.id')
            ->select(
                'p.id',
                'p.no_kontrak as no_kontrak', // Gunakan alias yang unik agar tidak bentrok dengan p.id
                'p.anggota_id',
                'a.nama_lengkap as nama',
                'p.nominal_realisasi as realisasi',
                'p.tenor',
                'p.angsuran_per_bulan as pokok',
                'p.tanggal_cair',
                DB::raw('DATE(p.jatuh_tempo) as jatuhTempo'),
                'p.sisa_tenor as sisaTenor',
                'p.total_bayar',
                'p.total_tagihan',
                'p.status'
            )
            ->where('p.status', '=', 'aktif')
            ->whereColumn('p.total_bayar', '<', 'p.total_tagihan')
            ->orderBy('p.tanggal_cair', 'desc');

        // 2. Filter Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('a.nama_lengkap', 'like', '%' . $searchTerm . '%')
                ->orWhere('p.no_kontrak', 'like', '%' . $searchTerm . '%'); // Sesuaikan tabelnya (p atau a?)
            });
        }

        // 3. Eksekusi Paginasi
        // Ini akan mengembalikan objek LengthAwarePaginator
        $perPage = $request->query('per_page', 10); // Default 10 data per halaman
        $pinjamanAktif = $query->paginate($perPage);

        // 4. Transformasi Data (Mapping)
        // Gunakan setCollection untuk mengubah data di dalam paginator tanpa merusak struktur paginasi
        $pinjamanAktif->setCollection($pinjamanAktif->getCollection()->map(function ($item) {
            $biayaAdmin = $item->realisasi * 0.01;
            $totalBayarPerBulan = $item->pokok + $biayaAdmin;
            $sisaHutang = $item->total_tagihan - $item->total_bayar;
            
            // Hitung Sisa Tenor Manual
            $item->sisaTenorManual = ($totalBayarPerBulan > 0) 
                ? ceil($sisaHutang / $totalBayarPerBulan) 
                : 0;

            $item->sisaTagihan = $sisaHutang;

            $item->sisaHutang = $sisaHutang;

            return $item;
        }));

        // 5. Kembalikan response JSON (Struktur paginasi Laravel otomatis disertakan)
        return response()->json($pinjamanAktif);
    }

    public function search(Request $request)
    {
        $search = $request->query('search');
        $query = DB::table('pinjamans as p')
            ->join('anggotas as a', 'p.anggota_id', '=', 'a.id')
            ->select(
                'p.no_kontrak as no_kontrak',
                'a.nama_lengkap as nama',
            )
            ->where('p.status', '=', 'aktif')
            ->orderBy('p.tanggal_cair', 'desc');

        // 2. Filter Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($search) {
                $q->where('a.nama_lengkap', 'like', '%' . $search . '%')
                ->orWhere('p.no_kontrak', 'like', '%' . $search . '%'); // Sesuaikan tabelnya (p atau a?)
            });
        }

        // 3. Eksekusi Paginasi
        // Ini akan mengembalikan objek LengthAwarePaginator
        $data = $query->limit(20)->get();

        // 5. Kembalikan response JSON (Struktur paginasi Laravel otomatis disertakan)
        return response()->json($data);
    }

    public function history($id)
    {
        try {
            // Mengambil riwayat angsuran menggunakan Query Builder
            $history = DB::table('transaksis')
                ->where('angsuran_id', $id)
                ->where('jenis_transaksi', TransaksiEnum::ANGSURAN->value) // Sesuaikan dengan string di DB Anda
                ->orderBy('created_at', 'desc')
                ->get();

            // Transformasi data untuk frontend (SFC Modular)
            $formattedHistory = $history->map(function ($item, $key) use ($history) {
                return [
                    'id' => $item->id,
                    'tanggal_bayar' => date('d M Y', strtotime($item->tanggal_transaksi)),
                    'total_bayar' => (int) $item->debit, // Angsuran masuk ke kredit/kas masuk
                    'angsuran_ke' => count($history) - $key, // Hitung mundur angsuran
                    'keterangan' => $item->keterangan,
                    'is_lunas' => str_contains(strtolower($item->keterangan), 'lunas'),
                    'payment_method' => $item->payment_method
                ];
            });

            return response()->json([
                'status' => 'success',
                'history' => $formattedHistory
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        $pinjaman = DB::table('pinjamans')
            ->join('anggotas', 'pinjamans.anggota_id', '=', 'anggotas.id')
            ->select(
                'pinjamans.id',
                'pinjamans.no_kontrak',
                'pinjamans.angsuran_per_bulan',
                'pinjamans.nominal_realisasi',
                'pinjamans.sisa_tenor',
                'pinjamans.total_bayar',
                'pinjamans.total_tagihan',
                'pinjamans.tenor',
                'anggotas.nama_lengkap as nama_anggota',
                'anggotas.no_anggota as no_anggota'
            )
            ->where('no_kontrak', $id)
            ->first();

        // Cek jika data tidak ditemukan
        if (!$pinjaman) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Hitung simulasi (1% bunga dari realisasi)
        $bungaEstimasi = $pinjaman->nominal_realisasi * 0.01;

        $sisaTagihan = $pinjaman->total_tagihan - $pinjaman->total_bayar;
        
        $sisaTenor = (int)($sisaTagihan / ($pinjaman->angsuran_per_bulan + $bungaEstimasi));
        $angsuranke = (int)($pinjaman->tenor - $sisaTenor) + 1;
        // $totalTagihan = $pinjaman->angsuran_per_bulan + $bungaEstimasi;

        return response()->json([
            'id'             => $pinjaman->id,
            'no_kontrak'     => $pinjaman->no_kontrak,
            'nama_anggota'   => $pinjaman->nama_anggota,
            'pokok'          => (float) $pinjaman->angsuran_per_bulan,
            'sisa_tenor'     => $pinjaman->sisa_tenor,
            'bunga_estimasi' => $bungaEstimasi,
            'nominal_realisasi' => $pinjaman->nominal_realisasi,
            // 'total_tagihan'  => $totalTagihan,
            'total_tagihan'  => $pinjaman->total_tagihan,
            'total_bayar'    => $pinjaman->total_bayar,
            'angsuranke'     => $angsuranke,
            'sisaTenor'      => $sisaTenor,
            'no_anggota'     => $pinjaman->no_anggota
        ]);
    }

    public function bayar(Request $request) {
        $validated = $request->validate([
            'loan_id' => 'required',
            'is_lunas' => 'required|boolean',
            'nominal_pokok' => 'required|numeric',
            'nominal_bunga' => 'required|numeric',
            // 'total_bayar' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'payment_method' => 'required|string'
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Ambil data pinjaman (tanap Eloquent)
            $loan = DB::table('pinjamans')->where('no_kontrak', $validated['loan_id'])->first();

            if (!$loan) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // 2. Simpan Transaksi (Uang Masuk ke Koperasi)
            DB::table('transaksis')->insert([
                'anggota_id' => $loan->anggota_id,
                'kode_transaksi' => TransaksiEnum::ANGSURAN->prefix().now()->format('YmdHis'),
                'tanggal_transaksi' => now()->format('Y-m-d'),
                'debit' =>  (int)$validated['amount_paid'],
                'jenis_transaksi' => 'angsuran',
                'kredit' => 0,
                'keterangan' => $validated['is_lunas'] ? "Pelunasan Pinjaman #{$loan->no_kontrak}" : "Angsuran Pinjaman #{$loan->no_kontrak}",
                'created_at' => now(),
                'angsuran_id' => $validated['loan_id'],
                'payment_method' => $validated['payment_method']
            ]);

            // 3. Update Status Pinjaman
            if ($validated['is_lunas']) {
                // Jika lunas, sisa tenor jadi 0 dan status selesai
                DB::table('pinjamans')
                    ->where('id', $loan->id)
                    ->update([
                        'sisa_tenor' => 0,
                        'status' => 'selesai',
                        'updated_at' => now()
                    ]);
            } else {
                // Jika angsuran biasa, kurangi sisa tenor 1
                DB::table('pinjamans')
                    ->where('id', $loan->id)
                    ->update([
                        'total_bayar' => DB::raw('total_bayar + ' . (int)$validated['amount_paid']),
                        'sisa_tenor' => DB::raw('sisa_tenor - 1'),
                        'updated_at' => now() // Opsional: update timestamp
                    ]);

                // Jika setelah dikurangi ternyata sisa tenor 0, otomatis selesaikan
                if ($loan->sisa_tenor - 1 <= 0) {
                    DB::table('pinjamans')->where('id', $loan->id)->update(['status' => 'selesai']);
                }
            }

            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil']);
        });
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'File tidak valid'], 422);
        }

        try {
            Excel::import(new PinjamanImport(), $request->file('file'));
            
            return response()->json([
                'message' => 'Data pinjaman berhasil diimport!'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importAngsuran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'File tidak valid'], 422);
        }

        try {
            Excel::import(new AngsuranImport(), $request->file('file'));
            
            return response()->json([
                'message' => 'Data angsuran berhasil diimport!'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        $fileName = 'Data Pinjaman Koperasi.xlsx';
        return Excel::download(new PinjamanExport(), $fileName);        
    }
}
