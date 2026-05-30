<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\BarangkeluarModel;
use App\Models\Admin\BarangmasukModel;
use App\Models\Admin\BarangModel;
use App\Models\Admin\WebModel;
use App\Models\Admin\CustomerModel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use PDF;

class LapStokBarangController extends Controller
{
    public function index(Request $request)
    {
        $data["title"] = "Lap Stok Barang";
        $data["customer"] = CustomerModel::orderBy('customer_nama', 'ASC')->get(); 
        return view('Admin.Laporan.StokBarang.index', $data);
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {

            // ✅ Jika ada filter ruangan, ambil hanya barang di ruangan itu
            if ($request->customer_id) {
                $data = \DB::table('tbl_barang')
                    ->join('tbl_barang_ruangan', 'tbl_barang.barang_kode', '=', 'tbl_barang_ruangan.barang_kode')
                    ->where('tbl_barang_ruangan.customer_id', $request->customer_id)
                     ->where('tbl_barang_ruangan.stok', '>', 0) // ✅ tambah ini
                    ->orderBy('tbl_barang.barang_nama', 'ASC')
                    ->select('tbl_barang.*')
                    ->get();
            } else {
                // ✅ Tidak ada filter → tampilkan semua barang
                $data = \DB::table('tbl_barang')->orderBy('barang_nama', 'ASC')->get();
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('customer_nama', function ($row) use ($request) {
                    if ($request->customer_id) {
                        $ruangan = \DB::table('tbl_customer')->where('customer_id', $request->customer_id)->first();
                        return $ruangan->customer_nama ?? '-';
                    }
                    return 'Semua Ruangan';
                })
                ->addColumn('jmlmasuk', function ($row) use ($request) {
                    $q = \DB::table('tbl_barangmasuk')->where('barang_kode', $row->barang_kode);
                    
                    if ($request->customer_id) {
                        $q->where('customer_id', $request->customer_id);
                    }
                    if ($request->tglawal && $request->tglakhir) {
                        $q->whereBetween('bm_tanggal', [$request->tglawal, $request->tglakhir]);
                    }
                    
                    return $q->sum('bm_jumlah') ?? 0;
                })
                ->addColumn('jmlkeluar', function ($row) use ($request) {
                    $q = \DB::table('tbl_barangkeluar')->where('barang_kode', $row->barang_kode);
                    
                    if ($request->customer_id) {
                        $q->where('bk_tujuan', $request->customer_id); // ✅ pakai bk_tujuan
                    }
                    if ($request->tglawal && $request->tglakhir) {
                        $q->whereBetween('bk_tanggal', [$request->tglawal, $request->tglakhir]);
                    }
                    
                    return $q->sum('bk_jumlah') ?? 0;
                })
                ->addColumn('totalstok', function ($row) use ($request) {
                    $qIn  = \DB::table('tbl_barangmasuk')->where('barang_kode', $row->barang_kode);
                    $qOut = \DB::table('tbl_barangkeluar')->where('barang_kode', $row->barang_kode);

                    if ($request->customer_id) {
                        $qIn->where('customer_id', $request->customer_id);
                        $qOut->where('bk_tujuan', $request->customer_id); // ✅ pakai bk_tujuan
                    }
                    if ($request->tglawal && $request->tglakhir) {
                        $qIn->whereBetween('bm_tanggal', [$request->tglawal, $request->tglakhir]);
                        $qOut->whereBetween('bk_tanggal', [$request->tglawal, $request->tglakhir]);
                    }

                    $masuk  = $qIn->sum('bm_jumlah') ?? 0;
                    $keluar = $qOut->sum('bk_jumlah') ?? 0;
                    $stok   = $masuk - $keluar;

                    $color = $stok <= 0 ? 'text-danger' : 'text-success';
                    return '<span class="' . $color . ' fw-bold">' . $stok . '</span>';
                })
                ->rawColumns(['totalstok'])
                ->make(true);
        }
    }

   public function print(Request $request)
{
    // Ambil data barang
    if ($request->customer_id) {
        $barang = \DB::table('tbl_barang')
            ->join('tbl_barang_ruangan', 'tbl_barang.barang_kode', '=', 'tbl_barang_ruangan.barang_kode')
            ->where('tbl_barang_ruangan.customer_id', $request->customer_id)
            ->orderBy('tbl_barang.barang_nama', 'ASC')
            ->select('tbl_barang.*')
            ->get();
    } else {
        $barang = BarangModel::orderBy('barang_nama', 'ASC')->get();
    }

    // ✅ Hitung stok langsung di controller, bukan di blade
    $data['data'] = $barang->map(function($d) use ($request) {
        $masuk  = \DB::table('tbl_barangmasuk')->where('barang_kode', $d->barang_kode);
        $keluar = \DB::table('tbl_barangkeluar')->where('barang_kode', $d->barang_kode);

        if ($request->customer_id) {
            $masuk->where('customer_id', $request->customer_id);
            $keluar->where('bk_tujuan', $request->customer_id);
        }
        if ($request->tglawal && $request->tglakhir) {
            $masuk->whereBetween('bm_tanggal', [$request->tglawal, $request->tglakhir]);
            $keluar->whereBetween('bk_tanggal', [$request->tglawal, $request->tglakhir]);
        }

        $d->jml_masuk  = $masuk->sum('bm_jumlah');
        $d->jml_keluar = $keluar->sum('bk_jumlah');
        $d->total_stok = $d->jml_masuk - $d->jml_keluar;
        return $d;
    });

    $data["title"]       = "Print Stok Barang";
    $data['web']         = WebModel::first();
    $data['tglawal']     = $request->tglawal;
    $data['tglakhir']    = $request->tglakhir;
    $data['customer_id'] = $request->customer_id;
    $data['customer']    = $request->customer_id
        ? \DB::table('tbl_customer')->where('customer_id', $request->customer_id)->first()
        : null;

    return view('Admin.Laporan.StokBarang.print', $data);
}

public function pdf(Request $request)
{
    if ($request->customer_id) {
        $barang = \DB::table('tbl_barang')
            ->join('tbl_barang_ruangan', 'tbl_barang.barang_kode', '=', 'tbl_barang_ruangan.barang_kode')
            ->where('tbl_barang_ruangan.customer_id', $request->customer_id)
            ->orderBy('tbl_barang.barang_nama', 'ASC')
            ->select('tbl_barang.*')
            ->get();
    } else {
        $barang = BarangModel::orderBy('barang_nama', 'ASC')->get();
    }

    $data['data'] = $barang->map(function($d) use ($request) {
        $masuk  = \DB::table('tbl_barangmasuk')->where('barang_kode', $d->barang_kode);
        $keluar = \DB::table('tbl_barangkeluar')->where('barang_kode', $d->barang_kode);

        if ($request->customer_id) {
            $masuk->where('customer_id', $request->customer_id);
            $keluar->where('bk_tujuan', $request->customer_id);
        }
        if ($request->tglawal && $request->tglakhir) {
            $masuk->whereBetween('bm_tanggal', [$request->tglawal, $request->tglakhir]);
            $keluar->whereBetween('bk_tanggal', [$request->tglawal, $request->tglakhir]);
        }

        $d->jml_masuk  = $masuk->sum('bm_jumlah');
        $d->jml_keluar = $keluar->sum('bk_jumlah');
        $d->total_stok = $d->jml_masuk - $d->jml_keluar;
        return $d;
    });

    $data["title"]       = "PDF Stok Barang";
    $data['web']         = WebModel::first();
    $data['tglawal']     = $request->tglawal;
    $data['tglakhir']    = $request->tglakhir;
    $data['customer_id'] = $request->customer_id;
    $data['customer']    = $request->customer_id
        ? \DB::table('tbl_customer')->where('customer_id', $request->customer_id)->first()
        : null;

    $pdf = PDF::loadView('Admin.Laporan.StokBarang.pdf', $data);
    return $pdf->download('lap-stok-barang.pdf');
}
}