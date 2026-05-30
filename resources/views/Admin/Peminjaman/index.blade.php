@extends('Master.Layouts.app', ['title' => $title])

@section('content')
<div class="page-header">
    <h1 class="page-title">{{$title}}</h1>
    <div>
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-gray">Transaksi</li>
            <li class="breadcrumb-item active">{{$title}}</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header justify-content-between">
                <h3 class="card-title">Data Peminjaman</h3>
                <div>
                    <a class="modal-effect btn btn-primary-light"
   data-bs-effect="effect-super-scaled"
   data-bs-toggle="modal"
   href="#modaldemo8"
   onclick="generateID()">
    Tambah Data <i class="fe fe-plus"></i>
</a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-1" class="table table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th width="1%">No</th>
                                <th>Kode</th>
                                <th>Peminjam</th>
                                <th>Ruangan</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Dikembalikan</th>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th width="1%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Pengembalian --}}
<div class="modal fade" id="modalProsesKembali" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fe fe-rotate-ccw me-2"></i>Konfirmasi Pengembalian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formProsesKembali">
                @csrf
                <input type="hidden" name="id_peminjaman" id="id_peminjaman_kembali">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fe fe-info me-1"></i>
                        Masukkan jumlah barang yang benar-benar dikembalikan saat ini.
                    </div>
                    <div id="render-barang-kembali"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i> Batal
                    </button>
                    <button type="submit" id="btnSimpanKembali" class="btn btn-success">
                        <i class="fe fe-check me-1"></i> Simpan Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('Admin.Peminjaman.tambah')
@include('Admin.Peminjaman.barang')
@include('Admin.Peminjaman.hapus')
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var table;

    $(document).ready(function () {
        // 1. Inisialisasi DataTable
        table = $('#table-1').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('peminjaman.get') }}",
            ordering: false,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'pinjam_kode', name: 'pinjam_kode' },
                { data: 'pinjam_nama', name: 'pinjam_nama' },
                { data: 'customer', name: 'customer' },
                { data: 'pinjam_tanggal', name: 'pinjam_tanggal' },
                { data: 'tanggal_dikembalikan', name: 'tanggal_dikembalikan' },
                { data: 'barang', name: 'barang' },
                { data: 'jumlah', name: 'jumlah' },
                { data: 'pinjam_status', name: 'pinjam_status' },
                { data: 'action', name: 'action' }
            ]
        });

        // 2. Submit Form Pengembalian
        $('#formProsesKembali').on('submit', function (e) {
            e.preventDefault();
            let id = $('#id_peminjaman_kembali').val();

            $.ajax({
                url: "{{ url('admin/peminjaman/kembalikan') }}/" + id,
                type: "POST",
                data: $(this).serialize(),
                beforeSend: function () {
                    $('#btnSimpanKembali').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...'
                    );
                },
                success: function (res) {
                    $('#modalProsesKembali').modal('hide');
                    table.ajax.reload(null, false);

                    // ✅ Alert sukses yang bagus
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.msg,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    // ✅ Baca pesan error dari response JSON controller
                    let res = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: res && res.msg 
                            ? res.msg  
                            : 'Terjadi kesalahan pada server.',
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function () {
                    $('#btnSimpanKembali').prop('disabled', false).html(
                        '<i class="fe fe-check me-1"></i> Simpan Pengembalian'
                    );
                }
            });
        });
    });

    // 3. Fungsi modal pengembalian
    function kembalikan(id) {
        $.get("{{ url('admin/peminjaman/detail') }}/" + id, function (data) {
            $('#id_peminjaman_kembali').val(id);
            let html = '';

            data.details.forEach(function (item) {
                let sudahKembali = item.jumlah_kembali ?? 0;
                let sisaPinjam   = item.jumlah - sudahKembali;

                html += `
                    <div class="form-group mb-3 p-3 border rounded shadow-sm">
                        <label class="fw-bold mb-2 d-block">
                            <i class="fe fe-box me-1 text-primary"></i>${item.barang.barang_nama}
                        </label>
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge bg-secondary">Total Pinjam: ${item.jumlah}</span>
                            <span class="badge bg-success">Sudah Kembali: ${sudahKembali}</span>
                            <span class="badge bg-warning text-dark">Sisa: ${sisaPinjam}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fe fe-package me-1"></i> Kembali
                            </span>
                            <input type="number" 
                                   name="jumlah_kembali[]" 
                                   class="form-control" 
                                   value="${sisaPinjam}" 
                                   min="0" 
                                   max="${sisaPinjam}" 
                                   required>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            Maksimal yang bisa dikembalikan sekarang: <strong>${sisaPinjam}</strong>
                        </small>
                    </div>
                `;
            });

            $('#render-barang-kembali').html(html);
            $('#modalProsesKembali').modal('show');
        });
    }

    function generateID() {
        let kode = "PMJ-" + Date.now();
        $("input[name='pinjam_kode']").val(kode);
    }

    function hapus(data) {
        $("input[name='idpeminjaman']").val(data.pinjam_id);
        $("#vpeminjaman").html("<b>" + data.pinjam_kode + "</b>");
        $("#modalHapus").modal('show');
    }
</script>
@endsection