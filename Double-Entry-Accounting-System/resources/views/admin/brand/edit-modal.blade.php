<!-- Edit Brand -->
<div class="modal fade" id="edit-brand">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Edit Brand</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="editBrandForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="edit_brand_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo (Image)</label>
                        <input type="file" name="logo" class="form-control" id="edit_brand_logo" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" id="edit_brand_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-2 btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Edit Brand -->
<script>
    var editModal = document.getElementById('edit-brand');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var name = button.getAttribute('data-name');
            var description = button.getAttribute('data-description');
            // ...other fields...

            document.getElementById('edit_brand_name').value = name;
            document.getElementById('edit_brand_description').value = description;
            // ...other fields...
        });
    }
</script>