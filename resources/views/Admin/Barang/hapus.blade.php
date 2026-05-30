<div class="modal fade" data-bs-backdrop="static" id="Hmodaldemo8">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-body text-center p-4 pb-5">
                <button type="button" aria-label="Close" onclick="resetH()" class="btn-close position-absolute" data-bs-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
                <br>
                <i class="icon icon-exclamation fs-70 text-warning lh-1 my-5 d-inline-block"></i>
                <h3 class="mb-5">Yakin hapus <span id="vbarang"></span> ?</h3>
                
                <input type="hidden" name="idbarang" id="idbarang">
                
                <button class="btn btn-danger-light pd-x-25 d-none" id="btnLoaderH" type="button" disabled="">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Loading...
                </button>
                <button onclick="submitFormH()" class="btn btn-danger-light pd-x-25" id="btnSubmit">Iya</button>
                <button type="button" onclick="resetH()" data-bs-dismiss="modal" class="btn btn-default pd-x-25">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    function submitFormH() {
        setLoadingH(true);
        // Mengambil ID dari input hidden idbarang
        const id = $("input[name='idbarang']").val();
        
        $.ajax({
            type: 'POST',
            url: "{{ url('admin/barang/proses_hapus') }}/" + id,
            dataType: 'json',
            success: function(data) {
                // 1. Tutup modal secara aman
                $('#Hmodaldemo8').modal('hide');
                
                // 2. Reload DataTable tanpa menghilangkan posisi halaman (null, false)
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                } else {
                    $('#table-1').DataTable().ajax.reload(null, false);
                }
                
                // 3. Panggil fungsi validasi bawaan dari index.blade.php kamu
                if (typeof validasi === 'function') {
                    validasi("Berhasil dihapus!", "success");
                } else {
                    alert("Data berhasil dihapus!");
                }
                
                resetH();
            },
            error: function(xhr, status, error) {
                setLoadingH(false);
                alert("Gagal menghapus data atau data tidak ditemukan.");
                console.error(xhr.responseText);
            }
        });
    }

    function resetH() {
        $("input[name='idbarang']").val('');
        setLoadingH(false);
    }

    function setLoadingH(bool) {
        if (bool == true) {
            $('#btnLoaderH').removeClass('d-none');
            $('#btnSubmit').addClass('d-none');
        } else {
            $('#btnSubmit').removeClass('d-none');
            $('#btnLoaderH').addClass('d-none');
        }
    }
</script>