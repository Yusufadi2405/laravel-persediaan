<!-- MODAL TAMBAH -->
<div class="modal fade" data-bs-backdrop="static" id="modaldemo8">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Tambah Barang Peminjaman</h6>
                <button aria-label="Close" onclick="reset()" class="btn-close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kode Peminjaman</label>
                            <input type="text" name="pinjam_kode" readonly class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Pinjam</label>
                            <input type="date" name="tanggal" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Nama Peminjam <span class="text-danger">*</span></label>
                            <input type="text" name="pinjam_nama" class="form-control" placeholder="Masukkan nama orang..." required>
                        </div>
                        <div class="form-group">
                            <label>Ruangan</label>
                            <select name="customer" id="ruangan" class="form-control">
                                <option value="">Pilih Ruangan</option>
                                @foreach ($customer as $c)
                                    <option value="{{ $c->customer_id }}">{{ $c->customer_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kode Barang <span class="text-danger me-1">*</span>
                                <input type="hidden" id="status" value="false">
                                <div class="spinner-border spinner-border-sm d-none" id="loaderkd" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" autocomplete="off" name="kdbarang" placeholder="">
                                <button class="btn btn-primary-light" onclick="searchBarang()" type="button"><i class="fe fe-search"></i></button>
                                <button class="btn btn-success-light" onclick="modalBarang()" type="button"><i class="fe fe-box"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" class="form-control" id="nmbarang" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Satuan</label>
                                    <input type="text" class="form-control" id="satuan" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis</label>
                                    <input type="text" class="form-control" id="jenis" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="jml" class="form-label">Jumlah Dipinjam <span class="text-danger">*</span></label>
                            <input type="text" name="jml" value="0" class="form-control"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary d-none" id="btnLoader" type="button" disabled>
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Loading...
                </button>
                <a href="javascript:void(0)" onclick="checkForm()" id="btnSimpan" class="btn btn-primary">
                    Simpan <i class="fe fe-check"></i>
                </a>
                <a href="javascript:void(0)" class="btn btn-light" onclick="reset()" data-bs-dismiss="modal">
                    Batal <i class="fe fe-x"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@section('formTambahJS')
<script>
    $('input[name="kdbarang"]').keypress(function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            getbarangbyid($('input[name="barang[]"]').val());
        }
    });

function modalBarang() {
    let ruangan = $("#ruangan").val();
    if (!ruangan) {
        Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Pilih ruangan terlebih dahulu!' });
        return;
    }
    table2.ajax.reload();
    $('#modaldemo8').modal('hide');
    setTimeout(function() {
        $('#modalBarang').modal('show');
    }, 400);
}

    function searchBarang() {
        getbarangbyid($('input[name="kdbarang"]').val());
        resetValid();
    }

    function getbarangbyid(id) {
        $("#loaderkd").removeClass('d-none');
        $.ajax({
            type: 'GET',
            url: "/admin/barang/getbarang/" + id,
            dataType: 'json',
            success: function(data) {
                $("#loaderkd").addClass('d-none');
                if (data.length > 0) {
                    $("#status").val("true");
                    $("#nmbarang").val(data[0].barang_nama);
                    $("#satuan").val(data[0].satuan_nama ?? '');
                    $("#jenis").val(data[0].jenisbarang_nama ?? '');
                } else {
                    $("#status").val("false");
                    $("#nmbarang").val('');
                    $("#satuan").val('');
                    $("#jenis").val('');
                }
            }
        });
    }

    function checkForm() {
        const tanggal = $("input[name='tanggal']").val();
        const nama    = $("input[name='pinjam_nama']").val();
        const status  = $("#status").val();
        const jumlah  = $("input[name='jml']").val();

        setLoading(true);
        resetValid();

        if (tanggal == "") {
            Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Tanggal wajib diisi!' });
            setLoading(false);
            return false;
        } else if (nama == "") {
            Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Nama Peminjam wajib diisi!' });
            setLoading(false);
            return false;
        } else if (status == "false") {
            Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Barang wajib dipilih!' });
            setLoading(false);
            return false;
        } else if (jumlah == "" || jumlah == "0") {
            Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Jumlah wajib diisi!' });
            setLoading(false);
            return false;
        } else {
            submitForm();
        }
    }

    function submitForm() {
        $.ajax({
            type: 'POST',
            url: "{{ route('peminjaman.store') }}",
            data: {
                _token: "{{ csrf_token() }}",
                pinjam_kode: $("input[name='pinjam_kode']").val(),
                pinjam_nama: $("input[name='pinjam_nama']").val(),
                tanggal: $("input[name='tanggal']").val(),
                jatuh_tempo: $("input[name='jatuh_tempo']").val(),
                customer: $("select[name='customer']").val(),
                barang: [$("input[name='kdbarang']").val()],
                jumlah: [$("input[name='jml']").val()]
            },
            success: function() {
                $('#modaldemo8').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data peminjaman berhasil ditambah!',
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#table-1').DataTable().ajax.reload(null, false);
                reset();
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: res && res.msg ? res.msg : 'Terjadi kesalahan pada server.'
                });
                setLoading(false);
            }
        });
    }

    function resetValid() {
        $("input[name='tanggal']").removeClass('is-invalid');
        $("input[name='kdbarang[]']").removeClass('is-invalid');
        $("select[name='customer']").removeClass('is-invalid');
        $("input[name='jml[]']").removeClass('is-invalid');
    }

    function reset() {
        resetValid();
        $("input[name='pinjam_kode']").val('');
        $("input[name='pinjam_nama']").val('');
        $("input[name='tanggal']").val('');
        $("input[name='jatuh_tempo']").val('');
        $("input[name='kdbarang']").val('');
        $("select[name='customer']").val('');
        $("input[name='jml']").val('');
        $("#nmbarang").val('');
        $("#satuan").val('');
        $("#jenis").val('');
        $("#status").val('false');
        setLoading(false);
    }

    function setLoading(bool) {
        if (bool == true) {
            $('#btnLoader').removeClass('d-none');
            $('#btnSimpan').addClass('d-none');
        } else {
            $('#btnSimpan').removeClass('d-none');
            $('#btnLoader').addClass('d-none');
        }
    }
</script>
@endsection