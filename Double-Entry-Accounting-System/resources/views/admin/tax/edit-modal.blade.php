<!-- Edit Tax Modal -->
<div class="modal fade" id="edit-tax">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Edit Tax</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="editTaxForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="edit_tax_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" name="rate" class="form-control" id="edit_tax_rate" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_tax_status" name="status" value="1">
                            <label class="form-check-label" for="edit_tax_status">Active</label>
                        </div>
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
<!-- /Edit Tax Modal -->
<script>
    var editModal = document.getElementById('edit-tax');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var rate = button.getAttribute('data-rate');
            var status = button.getAttribute('data-status');

            document.getElementById('editTaxForm').action = "{{ url('taxes') }}/" + id;
            document.getElementById('edit_tax_name').value = name;
            document.getElementById('edit_tax_rate').value = rate;
            // Set toggle checked if status is 1
            document.getElementById('edit_tax_status').checked = (status == 1);
        });
    }
</script>