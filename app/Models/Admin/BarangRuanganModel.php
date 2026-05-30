<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangRuanganModel extends Model
{
    use HasFactory;

    protected $table = 'tbl_barang_ruangan';

    protected $fillable = [
        'barang_kode',
        'customer_id',
        'stok'
    ];

    // relasi ke barang
    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_kode', 'barang_kode');
    }

    // relasi ke ruangan (customer)
    public function ruangan()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'customer_id');
    }
}
