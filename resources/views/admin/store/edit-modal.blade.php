<!-- Edit Store -->
<div class="modal fade" id="edit-store">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Edit Store</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('stores.update', 0) }}" method="POST" id="editStoreForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Store Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="edit_store_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" id="edit_store_address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact No <span class="text-danger">*</span></label>
                        <input type="text" name="contact_no" class="form-control" id="edit_store_contact_no" required>
                    </div>
                    <div class="mb-0">
                        <div class="status-toggle modal-status d-flex justify-content-between align-items-center">
                            <span class="status-label">Status</span>
                            <input type="checkbox" id="edit_store_status" class="check" name="status" value="1">
                            <label for="edit_store_status" class="checktoggle"></label>
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
<!-- /Edit Store -->