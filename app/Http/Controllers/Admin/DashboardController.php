<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\BarangkeluarModel;
use App\Models\Admin\BarangmasukModel;
use App\Models\Admin\BarangModel;
use App\Models\Admin\CustomerModel;
use App\Models\Admin\JenisBarangModel;
use App\Models\Admin\MerkModel;
use App\Models\Admin\SatuanModel;
use App\Models\Admin\UserModel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Kunci utama agar variabel $title tidak eror lagi
        $data["title"] = "Dashboard";

        // =============================================
        // WIDGET DATA MASTER (Tetap Dihitung di Controller)
        // =============================================
        $data["jenis"]    = JenisBarangModel::count();
        $data["satuan"]   = SatuanModel::count();
        $data["merk"]     = MerkModel::count();
        $data["user"]     = UserModel::count();

        // =============================================
        // WIDGET UTAMA DASHBOARD
        // =============================================
        $data["barang"]   = BarangModel::count();
        $data["bm"]       = BarangmasukModel::count();
        $data["bk"]       = BarangkeluarModel::count();
        $data["customer"] = CustomerModel::count();

        // =============================================
        // 1. DATA 5 BARANG MASUK TERBARU
        // =============================================
        $data["transaksi_masuk"] = BarangmasukModel::leftJoin('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangmasuk.barang_kode')
            ->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangmasuk.customer_id')
            ->select(
                'tbl_barang.barang_nama',
                'tbl_customer.customer_nama as ruangan',
                'tbl_barangmasuk.bm_tanggal as tanggal'
            )
            ->orderBy('tbl_barangmasuk.bm_id', 'DESC')
            ->take(5)
            ->get();

        // =============================================
        // 2. DATA 5 BARANG KELUAR TERBARU
        // =============================================
        $data["transaksi_keluar"] = BarangkeluarModel::leftJoin('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangkeluar.barang_kode')
            ->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangkeluar.bk_tujuan')
            ->select(
                'tbl_barang.barang_nama',
                'tbl_customer.customer_nama as ruangan',
                'tbl_barangkeluar.bk_tanggal as tanggal'
            )
            ->orderBy('tbl_barangkeluar.bk_id', 'DESC')
            ->take(5)
            ->get();

        return view('Admin.Dashboard.index', $data);
    }
}