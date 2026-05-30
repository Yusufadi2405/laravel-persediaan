<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AksesModel;
use App\Models\Admin\BarangkeluarModel;
use App\Models\Admin\BarangModel;
use App\Models\Admin\CustomerModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BarangkeluarController extends Controller
{
    public function index()
    {
        $data["title"] = "Barang Keluar";

        $data["hakTambah"] = AksesModel::leftJoin(
            'tbl_submenu',
            'tbl_submenu.submenu_id',
            '=',
            'tbl_akses.submenu_id'
        )->where([
            'tbl_akses.role_id' => Session::get('user')->role_id,
            'tbl_submenu.submenu_judul' => 'Barang Keluar',
            'tbl_akses.akses_type' => 'create'
        ])->count();

        $data["customer"] = CustomerModel::orderBy('customer_id', 'DESC')->get();

        return view('Admin.BarangKeluar.index', $data);
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            $data = BarangkeluarModel::leftJoin(
                'tbl_barang',
                'tbl_barang.barang_kode',
                '=',
                'tbl_barangkeluar.barang_kode'
            )
            ->leftJoin(
                'tbl_customer',
                'tbl_customer.customer_id',
                '=',
                'tbl_barangkeluar.bk_tujuan'
            )
            ->select(
                'tbl_barangkeluar.*',
                'tbl_barang.barang_nama as nama_barang',
                'tbl_customer.customer_nama as nama_ruangan'
            )
            ->orderBy('tbl_barangkeluar.bk_id', 'DESC')
            ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tgl', function ($row) {
                    return $row->bk_tanggal == '' ? '-' : Carbon::parse($row->bk_tanggal)->translatedFormat('d F Y');
                })
                ->addColumn('tujuan', function ($row) {
                    return $row->nama_ruangan ?? '-';
                })
                ->addColumn('barang', function ($row) {
                    return $row->nama_barang ?? '-';
                })
                ->addColumn('keterangan', function ($row) {
                    return $row->bk_keterangan ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $array = array(
                        "bk_id" => $row->bk_id,
                        "bk_kode" => $row->bk_kode,
                        "barang_kode" => $row->barang_kode,
                        "bk_tanggal" => $row->bk_tanggal,
                        "bk_tujuan" => trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $row->bk_tujuan)),
                        "bk_jumlah" => $row->bk_jumlah,
                        "bk_keterangan" => $row->bk_keterangan
                    );
                    $button = '';
                    $hakEdit = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang Keluar', 'tbl_akses.akses_type' => 'update'))->count();
                    $hakDelete = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang Keluar', 'tbl_akses.akses_type' => 'delete'))->count();
                    if ($hakEdit > 0 && $hakDelete > 0) {
                        $button .= '
                        <div class="g-2">
                        <a class="btn modal-effect text-primary btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Umodaldemo8" data-bs-toggle="tooltip" data-bs-original-title="Edit" onclick=update(' . json_encode($array) . ')><span class="fe fe-edit text-success fs-14"></span></a>
                        <a class="btn modal-effect text-danger btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Hmodaldemo8" onclick=hapus(' . json_encode($array) . ')><span class="fe fe-trash-2 fs-14"></span></a>
                        </div>
                        ';
                    } else if ($hakEdit > 0 && $hakDelete == 0) {
                        $button .= '
                        <div class="g-2">
                            <a class="btn modal-effect text-primary btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Umodaldemo8" data-bs-toggle="tooltip" data-bs-original-title="Edit" onclick=update(' . json_encode($array) . ')><span class="fe fe-edit text-success fs-14"></span></a>
                        </div>
                        ';
                    } else if ($hakEdit == 0 && $hakDelete > 0) {
                        $button .= '
                        <div class="g-2">
                        <a class="btn modal-effect text-danger btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Hmodaldemo8" onclick=hapus(' . json_encode($array) . ')><span class="fe fe-trash-2 fs-14"></span></a>
                        </div>
                        ';
                    } else {
                        $button .= '-';
                    }
                    return $button;
                })
                ->rawColumns(['action', 'tgl', 'tujuan', 'barang'])->make(true);
        }
    }

    public function proses_tambah(Request $request)
    {
        DB::transaction(function () use ($request) {
            // 1️⃣ Cek stok ruangan dulu
            $stokRuangan = DB::table('tbl_barang_ruangan')
                ->where('barang_kode', $request->barang)
                ->where('customer_id', $request->tujuan)
                ->value('stok');

            if ($stokRuangan === null) {
                throw new \Exception("Data stok ruangan tidak ditemukan");
            }

            if ($stokRuangan < $request->jml) {
                throw new \Exception("Stok ruangan tidak cukup");
            }

            // 2️⃣ Simpan transaksi barang keluar
            BarangkeluarModel::create([
                'bk_tanggal'    => $request->tglkeluar,
                'bk_kode'       => $request->bkkode,
                'barang_kode'   => $request->barang,
                'bk_tujuan'     => $request->tujuan,
                'bk_jumlah'     => $request->jml,
                'bk_keterangan' => $request->keterangan,
            ]);

            // 3️⃣ Kurangi stok global
            BarangModel::where('barang_kode', $request->barang)
                ->decrement('barang_stok', $request->jml);

            // 4️⃣ Kurangi stok ruangan
            DB::table('tbl_barang_ruangan')
                ->where('barang_kode', $request->barang)
                ->where('customer_id', $request->tujuan)
                ->decrement('stok', $request->jml);
        });

        return response()->json(['success' => 'Berhasil']);
    }

    public function proses_ubah(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                // 1. Ambil data transaksi lama sebelum diubah (Menggunakan ID jauh lebih aman)
                $barangkeluar = BarangkeluarModel::findOrFail($id);

                $jumlahLama = $barangkeluar->bk_jumlah;
                $jumlahBaru = $request->jml;
                
                $ruanganLama = $barangkeluar->bk_tujuan;
                $ruanganBaru = $request->tujuan;

                // ===================================================
                // KONDISI A: JIKA RUANGAN/TUJUAN ASAL BARANG DIGANTI
                // ===================================================
                if ($ruanganLama != $ruanganBaru) {
                    
                    // a. Kembalikan stok ke ruangan LAMA (karena batal keluar dari sana)
                    DB::table('tbl_barang_ruangan')
                        ->where('barang_kode', $barangkeluar->barang_kode)
                        ->where('customer_id', $ruanganLama)
                        ->increment('stok', $jumlahLama);

                    // b. Cek apakah stok di ruangan BARU mencukupi sebelum dikurangi
                    $stokRuanganBaru = DB::table('tbl_barang_ruangan')
                        ->where('barang_kode', $request->barang)
                        ->where('customer_id', $ruanganBaru)
                        ->value('stok');

                    if ($stokRuanganBaru === null) {
                        throw new \Exception("Data stok untuk ruangan baru tidak ditemukan!");
                    }

                    if ($stokRuanganBaru < $jumlahBaru) {
                        throw new \Exception("Gagal ubah! Stok di ruangan baru tidak mencukupi untuk dikeluarkan.");
                    }

                    // c. Kurangi stok di ruangan BARU
                    DB::table('tbl_barang_ruangan')
                        ->where('barang_kode', $request->barang)
                        ->where('customer_id', $ruanganBaru)
                        ->decrement('stok', $jumlahBaru);

                    // d. Sesuaikan Stok di Gudang Utama (tbl_barang) jika jumlahnya juga berubah
                    $selisihGudang = $jumlahBaru - $jumlahLama;
                    if ($selisihGudang > 0) {
                        BarangModel::where('barang_kode', $request->barang)->decrement('barang_stok', $selisihGudang);
                    } elseif ($selisihGudang < 0) {
                        BarangModel::where('barang_kode', $request->barang)->increment('barang_stok', abs($selisihGudang));
                    }

                // ===================================================
                // KONDISI B: JIKA RUANGAN TETAP SAMA (CUMA JUMLAH BERUBAH)
                // ===================================================
                } else {
                    $selisih = $jumlahBaru - $jumlahLama;

                    if ($selisih > 0) {
                        // Jika jumlah baru lebih besar (barang keluar bertambah), cek stok ruangan dulu
                        $stokRuangan = DB::table('tbl_barang_ruangan')
                            ->where('barang_kode', $request->barang)
                            ->where('customer_id', $request->tujuan)
                            ->value('stok');

                        if ($stokRuangan < $selisih) {
                            throw new \Exception("Gagal ubah! Stok ruangan tidak mencukupi untuk penambahan barang keluar.");
                        }

                        BarangModel::where('barang_kode', $request->barang)->decrement('barang_stok', $selisih);
                        DB::table('tbl_barang_ruangan')
                            ->where('barang_kode', $request->barang)
                            ->where('customer_id', $request->tujuan)
                            ->decrement('stok', $selisih);

                    } elseif ($selisih < 0) {
                        // Jika jumlah baru lebih kecil (barang keluar berkurang), kembalikan sisa stok
                        $nilaiSelisih = abs($selisih);
                        BarangModel::where('barang_kode', $request->barang)->increment('barang_stok', $nilaiSelisih);
                        DB::table('tbl_barang_ruangan')
                            ->where('barang_kode', $request->barang)
                            ->where('customer_id', $request->tujuan)
                            ->increment('stok', $nilaiSelisih);
                    }
                }

                // 3. Update data transaksi barang keluar
                $barangkeluar->update([
                    'bk_tanggal'    => $request->tglkeluar,
                    'bk_kode'       => $request->bkkode,
                    'barang_kode'   => $request->barang,
                    'bk_tujuan'     => $request->tujuan,
                    'bk_jumlah'     => $request->jml,
                    'bk_keterangan' => $request->keterangan,
                ]);
            });

            return response()->json(['success' => 'Berhasil']);
        } catch (\Exception $e) {
            // Mengembalikan pesan error agar bisa ditangkap oleh alert SweetAlert / AJAX kamu
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function proses_hapus(Request $request, BarangkeluarModel $barangkeluar)
    {
        DB::transaction(function () use ($barangkeluar) {
            // 1️⃣ Kembalikan stok global
            BarangModel::where('barang_kode', $barangkeluar->barang_kode)
                ->increment('barang_stok', $barangkeluar->bk_jumlah);

            // 2️⃣ Kembalikan stok ruangan
            DB::table('tbl_barang_ruangan')
                ->where('barang_kode', $barangkeluar->barang_kode)
                ->where('customer_id', $barangkeluar->bk_tujuan)
                ->increment('stok', $barangkeluar->bk_jumlah);

            // 3️⃣ Hapus transaksi
            $barangkeluar->delete();
        });

        return response()->json(['success' => 'Berhasil']);
    }

    public function getBarangByRuangan($id)
    {
        $data = DB::table('tbl_barang_ruangan')
            ->join('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barang_ruangan.barang_kode')
            ->where('tbl_barang_ruangan.customer_id', $id)
            ->where('tbl_barang_ruangan.stok', '>', 0)
            ->select(
                'tbl_barang.barang_kode',
                'tbl_barang.barang_nama',
                'tbl_barang_ruangan.stok'
            )
            ->get();

        return response()->json($data);
    }
}