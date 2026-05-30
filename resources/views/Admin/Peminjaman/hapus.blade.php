<!-- resources/views/Admin/Peminjaman/hapus.blade.php -->
<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-labelledby="modalHapusLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('peminjaman.delete') }}" method="POST">
            @csrf
            <input type="hidden" name="idpeminjaman">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah anda yakin ingin menghapus <span id="vpeminjaman"></span> ?</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>
