<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\PeminjamanModel;


class PeminjamanModel extends Model
{
    protected $table = 'tbl_peminjaman';
    protected $primaryKey = 'pinjam_id';
  
    protected $fillable = [
        'pinjam_kode',
        'pinjam_nama',
        'customer_id',
        'pinjam_tanggal',
        'pinjam_jatuh_tempo',
  
        'pinjam_status',
        'pinjam_tanggal_kembali',
        
    ];



 public function details()
{
    return $this->hasMany(PeminjamanDetailModel::class, 'pinjam_id');
}

public function customer()
{
    return $this->belongsTo(CustomerModel::class, 'customer_id');
}

}

