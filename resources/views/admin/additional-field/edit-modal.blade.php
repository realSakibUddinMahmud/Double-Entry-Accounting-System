<!-- Add Additional Field -->
<div class="modal fade" id="add-field">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Add Additional Field</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('additional-fields.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Model Type <span class="text-danger">*</span></label>
                        <input type="text" name="model_type" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Field Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Select</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-2 btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Field</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add Additional Field -->

<!-- Edit Additional Field -->
<div class="modal fade" id="edit-field">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Edit Additional Field</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="editFieldForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Field For <span class="text-danger">*</span></label>
                        <select name="model_type" class="form-control" id="edit_field_model_type" required>
                            <option value="">-- Select Field For --</option>
                            <option value="product">Product</option>
                            <option value="sales">Sales</option>
                            <option value="purchase">Purchase</option>
                            <option value="customer">Customer</option>
                            <option value="supplier">Supplier</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Field Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="edit_field_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control" id="edit_field_label" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" id="edit_field_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="select">Select</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </div>
                    <div class="mb-3" id="editSelectOptionsDiv" style="display:none;">
                        <label class="form-label">Select Options <span class="text-danger">*</span></label>
                        <input type="text" name="options" class="form-control" id="edit_field_options" placeholder="Comma separated values, e.g. Red,Green,Blue">
                        <small class="text-muted">These will be stored as a comma separated value.</small>
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
<!-- /Edit Additional Field -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editTypeSelect = document.getElementById('edit_field_type');
    var editOptionsDiv = document.getElementById('editSelectOptionsDiv');
    var editOptionsInput = document.getElementById('edit_field_options');

    if(editTypeSelect) {
        editTypeSelect.addEventListener('change', function() {
            if(this.value === 'select') {
                editOptionsDiv.style.display = '';
            } else {
                editOptionsDiv.style.display = 'none';
                if(editOptionsInput) editOptionsInput.value = '';
            }
        });
    }

    var editModal = document.getElementById('edit-field');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('editFieldForm').action = "{{ url('additional-fields') }}/" + button.getAttribute('data-id');
            document.getElementById('edit_field_model_type').value = button.getAttribute('data-model_type');
            document.getElementById('edit_field_name').value = button.getAttribute('data-name');
            document.getElementById('edit_field_label').value = button.getAttribute('data-label');
            document.getElementById('edit_field_type').value = button.getAttribute('data-type');
            // Handle options for select type
            if(button.getAttribute('data-type') === 'select') {
                editOptionsDiv.style.display = '';
                editOptionsInput.value = button.getAttribute('data-options') || '';
            } else {
                editOptionsDiv.style.display = 'none';
                editOptionsInput.value = '';
            }
        });
    }
});
</script>