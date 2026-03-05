<?php

namespace App\Http\Controllers;

use App\Enums\TransaksiEnum;
use App\Imports\AnggotaImport;
use App\Models\Anggota;
use App\Models\Pinjaman;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        // Gunakan DB::table atau Model Anggota
        $query = DB::table('anggotas as a')
            ->leftJoin('transaksis as t', function($join) {
                $join->on('a.id', '=', 't.anggota_id')
                    ->where('t.jenis_transaksi', '=', TransaksiEnum::SUKARELA->value);
            })
            ->select(
                'a.id',
                'a.no_anggota',
                'a.nama_lengkap as nama',
                'a.pj',
                'a.saldo_wajib as wajib',
                'a.saldo_pokok as pokok',
                'a.saldo_khusus as khusus',
                'a.status',
                DB::raw('IFNULL(SUM(t.debit), 0) as sukarela')
            )
            // Tambahkan semua kolom non-agregasi ke groupBy
            ->groupBy('a.id', 'a.no_anggota', 'a.nama_lengkap', 'a.pj', 'a.saldo_wajib', 'a.saldo_pokok', 'a.saldo_khusus', 'a.status');

        // Filter Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('a.nama_lengkap', 'like', '%' . $searchTerm . '%')
                ->orWhere('a.no_anggota', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter Status (Sesuai parameter dari Vue)
        if ($request->filled('status')) {
            $query->where('a.status', $request->status);
        }

        // Filter Group/PJ
        if ($request->filled('group')) {
            $query->where('a.pj', $request->group);
        }

        // Sorting
        $query->orderBy('a.nama_lengkap', 'asc');

        // Paginate (Laravel otomatis menghandle offset & limit berdasarkan ?page=x)
        $perPage = $request->get('per_page', 12); // Default 12 agar pas dengan grid col-3
        $anggotas = $query->paginate($perPage);

        return response()->json($anggotas);
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
            Excel::import(new AnggotaImport(), $request->file('file'));
            
            return response()->json([
                'message' => 'Data anggota berhasil diimport!'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // 1. Validasi Input sesuai dengan object member di frontend
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'pj' => 'required|string|max:255',
            'pokok' => 'required|numeric|min:0',
            'wajib' => 'required|numeric|min:0',
            // Sukarela dan Khusus tidak diperlukan di input awal, 
            // nanti bertambah melalui transaksi
        ]);

        // 2. Gunakan DB Transaction untuk konsistensi data
        DB::transaction(function () use ($validated) {
            
            // 3. Generate No Anggota Otomatis
            $lastMember = Anggota::orderBy('id', 'desc')->first();
            $nextId = $lastMember ? $lastMember->id + 1 : 1;
            $noAnggota = 'ANGG-' . Carbon::now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // 4. Simpan data anggota
            $anggota = Anggota::create([
                'no_anggota' => $noAnggota,
                'nama_lengkap' => $validated['nama'],
                'pj' => $validated['pj'],
                'tanggal_daftar' => Carbon::now(),
                // saldo awal akan diupdate dari hasil transaksi
                'saldo_pokok' => $validated['pokok'],
                'saldo_wajib' => $validated['wajib'],
            ]);

            // 5. Simpan transaksi setoran awal (Pokok + Wajib)
            $totalSetoran = $validated['pokok'] + $validated['wajib'];
            
            if ($validated['pokok'] > 0) {
                // Simpan untuk mendapatkan ID
                Transaksi::create([
                    'anggota_id' => $anggota->id,
                    'kode_transaksi' => TransaksiEnum::POKOK->prefix() . "-" . Carbon::now()->format('YmdHis'), // Placeholder
                    'tanggal_transaksi' => Carbon::now(),
                    'jenis_transaksi' => TransaksiEnum::POKOK, // Menggunakan Enum
                    'debit' => (int)$validated['pokok'],
                    'kredit' => 0,
                    'keterangan' => 'Setoran Simpanan Pokok',
                ]);
            }
            if ($validated['wajib'] > 0) {
                // Simpan untuk mendapatkan ID
                Transaksi::create([
                    'anggota_id' => $anggota->id,
                    'kode_transaksi' => TransaksiEnum::WAJIB->prefix() . Carbon::now()->format('YmdHis'), // Placeholder
                    'tanggal_transaksi' => Carbon::now(),
                    'jenis_transaksi' => TransaksiEnum::WAJIB, // Menggunakan Enum
                    'debit' => (int)$validated['wajib'],
                    'kredit' => 0,
                    'keterangan' => 'Setoran Simpanan Wajib Awal Anggota Baru',
                ]);
            }
        });

        return response()->json(['message' => 'Anggota dan setoran awal berhasil disimpan'], 201);
    }

    public function showRiwayatTransaksi(Request $request, $anggotaId)
    {
        $anggotaDetail = DB::table('anggotas as a')
            // 1. Join untuk menjumlahkan transaksi sukarela
            ->leftJoin('transaksis as t', function($join) {
                $join->on('a.id', '=', 't.anggota_id')
                    // 2. Filter hanya transaksi sukarela pada saat join
                    ->where('t.jenis_transaksi', '=', 'sukarela'); // atau JenisTransaksi::SUKARELA->value
            })
            // 3. Pilih kolom dan hitung total_sukarela
            ->select(
                'a.id',
                'a.no_anggota',
                'a.nama_lengkap as nama',
                'a.pj',
                'a.saldo_pokok',
                'a.saldo_wajib',
                'a.saldo_khusus',
                // SUM(debit) adalah uang masuk, SUM(kredit) uang keluar
                // Hitung total saldo sukarela dari transaksi masuk - keluar
                DB::raw('IFNULL(SUM(t.debit), 0) - IFNULL(SUM(t.kredit), 0) as total_sukarela')
            )
            ->where('a.id', $anggotaId) // Filter berdasarkan ID anggota yang diklik
            // 4. GroupBy wajib untuk agregasi SUM
            ->groupBy('a.id', 'a.no_anggota', 'a.nama_lengkap', 'a.pj', 'a.saldo_pokok', 'a.saldo_wajib', 'a.saldo_khusus')
            ->first();

        $transaksis = DB::table('transaksis')
            ->where('anggota_id', $anggotaId)
            // ->orderBy('tanggal_transaksi', 'desc')
            ->orderBy('id', 'desc')
            ->get(); // Gunakan get() jika tidak butuh pagination di modal

        return response()->json([
            'transaksi' => $transaksis,
            'anggota' => $anggotaDetail   
        ]);
    }

    public function storeSimpanan(Request $request) {
        try {
            $validated = $request->validate([
                'memberId' => 'required|exists:anggotas,id',
                'tipe' => 'required|in:pokok,wajib,sukarela,khusus', // Validasi tipe
                'nominal' => 'required|numeric|min:1',
            ]);

            $member = DB::table('anggotas')->where('id', $validated['memberId'])->first();
            if ($member->status !== 'aktif') {
                return response()->json([
                    'message' => 'Transaksi gagal. Anggota atas nama ' . $member->nama . ' sudah tidak aktif.'
                ], 200);
            }

            if($validated['tipe'] == 'wajib') $validated['nominal'] = 30000;
            // Gunakan DB Transaction agar konsisten
            DB::transaction(function () use ($validated) {
                $tipeEnum = TransaksiEnum::from($validated['tipe']);
                // 1. Simpan ke tabel transaksis
                $transaksi = Transaksi::create([
                    'anggota_id' => $validated['memberId'],
                    'kode_transaksi' => $tipeEnum->prefix() . "-" . Carbon::now()->format('YmdHis'), 
                    'tanggal_transaksi' => Carbon::now(),
                    'jenis_transaksi' => $validated['tipe'], // Sesuaikan dengan enum/constraint di DB
                    'debit' => (int)$validated['nominal'], // Uang masuk
                    'kredit' => 0,
                    'keterangan' => 'Setoran Simpanan ' . ucfirst($validated['tipe']),
                ]);

                // 2. Update saldo di tabel anggotas
                // Sesuaikan nama kolom saldo dengan struktur database Anda
                $columnName = 'saldo_' . $validated['tipe']; 
                
                DB::table('anggotas')
                    ->where('id', $validated['memberId'])
                    ->increment($columnName, $validated['nominal']);
            });

            return response()->json(['message' => 'Transaksi berhasil disimpan'], 201);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeTarik(Request $request) {
        try {
            $validated = $request->validate([
                'memberId' => 'required|exists:anggotas,id',
                'nominal' => 'required|numeric|min:1',
            ]);

            $member = DB::table('anggotas')->where('id', $validated['memberId'])->first();

            if ($member->status !== 'aktif') {
                return response()->json([
                    'message' => 'Transaksi gagal. Anggota atas nama ' . $member->nama . ' sudah tidak aktif.'
                ], 200);
            }

            $totalSaldoTersedia = $member->saldo_sukarela + $member->saldo_wajib + $member->saldo_pokok + $member->saldo_khusus;
            if ($validated['nominal'] > $totalSaldoTersedia) {
                return response()->json([
                    'message' => 'Saldo tidak mencukupi. Total saldo tersedia: ' . number_format($totalSaldoTersedia, 0, ',', '.')
                ], 422);
            }

            
            // Gunakan DB Transaction agar konsisten
            DB::transaction(function () use ($validated) {
                $nominalTarik = $validated['nominal'];
                $memberId = $validated['memberId'];
                $member = DB::table('anggotas')->where('id', $memberId)->first();

                $tipeEnum = TransaksiEnum::TARIK;
                // 1. Simpan ke tabel transaksis
                $transaksi = Transaksi::create([
                    'anggota_id' => $validated['memberId'],
                    'kode_transaksi' => $tipeEnum->prefix() . "-" . Carbon::now()->format('YmdHis'), 
                    'tanggal_transaksi' => Carbon::now(),
                    'jenis_transaksi' => 'tarik', // Sesuaikan dengan enum/constraint di DB
                    'debit' => 0, // Uang masuk
                    'kredit' => (int)$nominalTarik,
                    'keterangan' => 'Tarik tunai',
                ]);

                $sisaPotong = $nominalTarik;

                // Potong Sukarela
                $potongSukarela = min($member->saldo_sukarela, $sisaPotong);
                $sisaPotong -= $potongSukarela;

                // Potong Wajib
                $potongWajib = min($member->saldo_wajib, $sisaPotong);
                $sisaPotong -= $potongWajib;

                // Potong Pokok
                $potongPokok = min($member->saldo_pokok, $sisaPotong);
                $sisaPotong -= $potongPokok;

                // Potong Khusus
                $potongKhusus = min($member->saldo_khusus, $sisaPotong);
                $sisaPotong -= $potongKhusus;

                // 4. Update Database
                DB::table('anggotas')->where('id', $memberId)->update([
                    'saldo_sukarela' => $member->saldo_sukarela - $potongSukarela,
                    'saldo_wajib' => $member->saldo_wajib - $potongWajib,
                    'saldo_pokok' => $member->saldo_pokok - $potongPokok,
                    'saldo_khusus' => $member->saldo_khusus - $potongKhusus
                ]);

                // 5. Cek Status Aktif (Jika semua saldo jadi 0, non-aktifkan)
                $memberBaru = DB::table('anggotas')->where('id', $memberId)->first();
                $totalSaldoAkhir = $memberBaru->saldo_sukarela + $memberBaru->saldo_wajib + $memberBaru->saldo_pokok + $memberBaru->saldo_khusus;

                if ($totalSaldoAkhir <= 0) {
                    DB::table('anggotas')->where('id', $memberId)->update(['status' => 'tidak_aktif']);
                }
            });

            $member = DB::table('anggotas')->where('id', $request->memberId)->first();
            return response()->json([
                'status' => 'success',
                'message' => 'Setoran berhasil',
                'data' => [
                    'memberId' => $member->id,
                    'status' => $member->status,
                    // Kirim semua saldo terbaru
                    'pokok' => (int) $member->saldo_pokok,
                    'wajib' => (int) $member->saldo_wajib,
                    'sukarela' => (int) $member->saldo_sukarela,
                    'khusus' => (int) $member->saldo_khusus,
                ]
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storePinjaman(Request $request)
    {
        try {
            $validated = $request->validate([
                'memberId' => 'required|exists:anggotas,id',
                'tanggalCair' => 'required|date',
                'realisasi' => 'required|numeric|min:1',
                'tenor' => 'required|integer|min:1',
                'angsuranPokok' => 'required|numeric',
            ]);

            $is_aktif = DB::table('anggotas')
                ->where('id', $validated['memberId'])
                ->where('status', 'aktif')
                ->exists();
            if(!$is_aktif) {
                return response()->json([
                    'gagal' => true,
                    'message' => 'Anggota tidak aktif'
                ]);
            }

            $exists = DB::table('pinjamans')
                ->where('anggota_id', $validated['memberId'])
                ->where('status', 'aktif')
                ->exists();

            if($exists) {
                return response()->json([
                    'gagal' => true,
                    'message' => 'Anggota masih memiliki pinjaman yang aktif'
                ], 200);
            }

            $loan = DB::transaction(function () use ($validated) {
                // 1. Hitung jatuh tempo
                $tanggalCair = Carbon::parse($validated['tanggalCair'])->setTimezone('Asia/Jakarta');
                $jatuhTempo = $tanggalCair->copy()->addMonths((int)$validated['tenor']);

                // 2. Simpan data pinjaman
                $bungaPersen = 0.01 * $validated['tenor']; // Contoh 1%
                $totalBunga = $validated['realisasi'] * $bungaPersen;
                $totalTagihan = $validated['realisasi'] + $totalBunga;

                $voucher = $validated['realisasi'] * 0.01;
                $khusus = $validated['realisasi'] * 0.01;
                $admin = $validated['realisasi'] * 0.01;

                $pencairan = $validated['realisasi'] - ($voucher + $khusus + $admin);

                $pinjaman = Pinjaman::create([
                    'anggota_id' => $validated['memberId'],
                    'no_kontrak' => TransaksiEnum::ANGSURAN->prefix() . '-' . Carbon::now()->format('YmdHis'),
                    'tanggal_cair' => Carbon::parse($tanggalCair)->format('Y-m-d'), 
                    'jatuh_tempo' => Carbon::parse($jatuhTempo)->format('Y-m-d'),
                    'nominal_realisasi' => $validated['realisasi'],
                    'tenor' => (int)$validated['tenor'],
                    'total_tagihan' => $totalTagihan,
                    'angsuran_per_bulan' => (int)$validated['angsuranPokok'],
                    'sisa_tenor' => $validated['tenor'],
                    'status' => 'aktif',
                ]);

                // 3. Simpan data transaksi pencairan (uang keluar dari kas koperasi)
                Transaksi::create([
                    'anggota_id' => $validated['memberId'],
                    'kode_transaksi' => TransaksiEnum::PENCAIRAN->prefix() . '-' . Carbon::now()->format('YmdHis'),
                    'tanggal_transaksi' => $tanggalCair,
                    'jenis_transaksi' => TransaksiEnum::PENCAIRAN->value,
                    'debit' => 0,
                    'kredit' => (int)$pencairan,
                    'keterangan' => 'Pencairan Pinjaman - ' . $pinjaman->id,
                ]);

                DB::table('anggotas')
                    ->where('id', $validated['memberId'])
                    ->increment('saldo_khusus', $validated['realisasi'] * 0.01);

                $anggota = DB::table('anggotas')
                    ->select('nama_lengkap as nama')
                    ->where('id', $validated['memberId'])
                    ->first();

                if ($anggota) {
                    $pinjaman['nama'] = $anggota->nama;
                }


                return $pinjaman;
            });

            return response()->json([
                'message' => 'Pinjaman berhasil dibuat',
                'loan' => [
                    'realisasi' => $loan->nominal_realisasi,
                    'angsuranPokok' => $loan->angsuran_per_bulan,
                    'jatuhTempo' => $loan->jatuh_tempo,
                    'nama' => $loan->nama,
                    'id' => $loan->no_kontrak
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
