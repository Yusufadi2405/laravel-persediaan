<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\BarangmasukModel;
use App\Models\Admin\WebModel;
use App\Models\Admin\CustomerModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use PDF;

class LapBarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $data["title"] = "Lap Barang Masuk";
        $data["ruangan"] = CustomerModel::all(); 
        return view('Admin.Laporan.BarangMasuk.index', $data);
    }

    public function print(Request $request)
    {
        // 1. Buat Query Dasar
        $query = BarangmasukModel::leftJoin('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangmasuk.barang_kode')
            ->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangmasuk.customer_id');

        // 2. Filter Tanggal
        if ($request->tglawal) {
            $query->whereBetween('bm_tanggal', [$request->tglawal, $request->tglakhir]);
        }

        // 3. Filter Ruangan (PENTING: Gunakan variabel $query yang sudah ada)
        if ($request->ruangan) {
            $query->where('tbl_barangmasuk.customer_id', $request->ruangan);
        }

        // 4. Urutan dari tanggal terdahulu
        $data['data'] = $query->orderBy('bm_tanggal', 'ASC')->get();

        $data["title"] = "Print Barang Masuk";
        $data['web'] = WebModel::first();
        $data['tglawal'] = $request->tglawal;
        $data['tglakhir'] = $request->tglakhir;
        return view('Admin.Laporan.BarangMasuk.print', $data);
    }

    public function pdf(Request $request)
    {
        // Sama seperti print, definisikan query dulu
        $query = BarangmasukModel::leftJoin('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangmasuk.barang_kode')
            ->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangmasuk.customer_id');

        if ($request->tglawal) {
            $query->whereBetween('bm_tanggal', [$request->tglawal, $request->tglakhir]);
        }

        if ($request->ruangan) {
            $query->where('tbl_barangmasuk.customer_id', $request->ruangan);
        }

        $data['data'] = $query->orderBy('bm_tanggal', 'ASC')->get();

        $data["title"] = "PDF Barang Masuk";
        $data['web'] = WebModel::first();
        $data['tglawal'] = $request->tglawal;
        $data['tglakhir'] = $request->tglakhir;
        
        $pdf = PDF::loadView('Admin.Laporan.BarangMasuk.pdf', $data);
        
        if($request->tglawal){
            return $pdf->download('lap-bm-'.$request->tglawal.'-'.$request->tglakhir.'.pdf');
        }else{
            return $pdf->download('lap-bm-semua-tanggal.pdf');
        }
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            $query = BarangmasukModel::leftJoin('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangmasuk.barang_kode')
                ->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangmasuk.customer_id')
                ->select(
                    'tbl_barangmasuk.*',
                    'tbl_barang.barang_nama',
                    'tbl_barang.barang_harga',
                    'tbl_customer.customer_nama'
                );

            if ($request->tglawal != '') {
                $query->whereBetween('bm_tanggal', [$request->tglawal, $request->tglakhir]);
            }

            if ($request->ruangan != '') {
                $query->where('tbl_barangmasuk.customer_id', $request->ruangan);
            }

            // Urutkan berdasarkan tanggal ASC (Terdahulu ke Terbaru)
            $data = $query->orderBy('bm_tanggal', 'ASC')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tgl', function ($row) {
                    return $row->bm_tanggal ? Carbon::parse($row->bm_tanggal)->translatedFormat('d F Y') : '-';
                })
                ->addColumn('customer', function ($row) {
                    return $row->customer_nama ?? '-';
                })
                ->addColumn('barang', function ($row) {
                    return $row->barang_nama ?? '-';
                })
                ->addColumn('harga_satuan', function ($row) {
                    return 'Rp ' . number_format($row->barang_harga ?? 0, 0, ',', '.');
                })
                ->addColumn('total_harga', function ($row) {
                    return 'Rp ' . number_format($row->bm_jumlah * ($row->barang_harga ?? 0), 0, ',', '.');
                })
                ->make(true);
        }
    }
}