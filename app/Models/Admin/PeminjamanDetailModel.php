<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class PeminjamanDetailModel extends Model
{
    protected $table = 'tbl_peminjaman_detail';
    protected $primaryKey = 'detail_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'pinjam_id',    
        'barang_kode',
        'jumlah',
        'jumlah_kembali'
    ];

    public $timestamps = true;

    // relasi ke barang
    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_kode', 'barang_kode');
    }
}
