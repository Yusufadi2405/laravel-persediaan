<!-- MODAL TAMBAH RUANGAN -->
<div class="modal fade" data-bs-backdrop="static" id="modaldemo8">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Tambah Ruangan</h6>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_ruangan" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Kode Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="kode_ruangan" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary d-none" id="btnLoader" disabled>
                    <span class="spinner-border spinner-border-sm"></span> Loading...
                </button>
                <button class="btn btn-primary" id="btnSimpan" onclick="checkForm()">Simpan</button>
                <button class="btn btn-light" data-bs-dismiss="modal" onclick="reset()">Batal</button>
            </div>
        </div>
    </div>
</div>

@section('formTambahJS')
<script>
function checkForm() {
    let nama = $("input[name='nama_ruangan']").val();
    let kode = $("input[name='kode_ruangan']").val();

    setLoading(true);
    resetValid();

    if (nama == '') {
        validasi('Nama Ruangan wajib diisi', 'warning');
        $("input[name='nama_ruangan']").addClass('is-invalid');
        setLoading(false);
        return;
    }

    if (kode == '') {
        validasi('Kode Ruangan wajib diisi', 'warning');
        $("input[name='kode_ruangan']").addClass('is-invalid');
        setLoading(false);
        return;
    }

    submitForm();
}

function submitForm() {
    $.ajax({
        type: 'POST',
        url: "{{ route('customer.store') }}",
        data: {
            customer: $("input[name='nama_ruangan']").val(),
            notelp: $("input[name='kode_ruangan']").val(),
            alamat: $("textarea[name='keterangan']").val()
        },
        success: function () {
            $('#modaldemo8').modal('hide');
            swal({ title: "Ruangan berhasil ditambahkan", type: "success" });
            table.ajax.reload(null, false);
            reset();
        }
    });
}

function resetValid() {
    $("input").removeClass('is-invalid');
}

function reset() {
    resetValid();
    $("input[name='nama_ruangan']").val('');
    $("input[name='kode_ruangan']").val('');
    $("textarea[name='keterangan']").val('');
    setLoading(false);
}

function setLoading(state) {
    if (state) {
        $('#btnLoader').removeClass('d-none');
        $('#btnSimpan').addClass('d-none');
    } else {
        $('#btnSimpan').removeClass('d-none');
        $('#btnLoader').addClass('d-none');
    }
}
</script>
@endsection
