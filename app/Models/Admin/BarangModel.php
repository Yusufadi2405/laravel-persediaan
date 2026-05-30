<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\BarangMasukModel;
use App\Models\Admin\BarangKeluarModel;
use App\Models\Admin\PeminjamanDetailModel;
class BarangModel extends Model
{
    use HasFactory;
    protected $table = "tbl_barang";
    protected $primaryKey = 'barang_id';
       protected $fillable = [
        'barang_gambar',
        'jenisbarang_id',
        'satuan_id',
        'merk_id',
        'barang_kode',
        'barang_nama',
        'barang_slug',
        'barang_harga',
        'barang_stok',
    ];

}

