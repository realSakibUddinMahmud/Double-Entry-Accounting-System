<!-- Edit Customer -->
<div class="modal fade" id="edit-customer">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Edit Customer</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="editCustomerForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="edit_customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" id="edit_customer_phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="edit_customer_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" id="edit_customer_address" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label><br>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_customer_status" name="status" value="1">
                            <label class="form-check-label" for="edit_customer_status" id="edit_customer_status_label">Active</label>
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
<!-- /Edit Customer -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('edit-customer');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var status = button.getAttribute('data-status');
            var statusInput = document.getElementById('edit_customer_status');
            var statusLabel = document.getElementById('edit_customer_status_label');

            if (status == 'Active' || status == '1' || status === true || status === 'true') {
                statusInput.checked = true;
                statusInput.value = '1';
                statusLabel.textContent = 'Active';
            } else {
                statusInput.checked = false;
                statusInput.value = '0';
                statusLabel.textContent = 'Archived';
            }

            statusInput.addEventListener('change', function() {
                if (this.checked) {
                    this.value = '1';
                    statusLabel.textContent = 'Active';
                } else {
                    this.value = '0';
                    statusLabel.textContent = 'Archived';
                }
            });
        });
    }
});
</script>
@endpush