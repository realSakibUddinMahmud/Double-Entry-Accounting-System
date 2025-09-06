<!-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/additional-field/view-modal.blade.php -->
<!-- View Additional Field Modal -->
<div class="modal fade" id="view-field" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Additional Field Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Model Type:</dt>
                    <dd class="col-sm-8" id="view_field_model_type"></dd>
                    <dt class="col-sm-4">Field Name:</dt>
                    <dd class="col-sm-8" id="view_field_name"></dd>
                    <dt class="col-sm-4">Label:</dt>
                    <dd class="col-sm-8" id="view_field_label"></dd>
                    <dt class="col-sm-4">Type:</dt>
                    <dd class="col-sm-8" id="view_field_type"></dd>
                    <dt class="col-sm-4" id="view_field_options_label" style="display:none;">Select Options:</dt>
                    <dd class="col-sm-8" id="view_field_options" style="display:none; white-space:pre; font-family:monospace;"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /View Additional Field Modal -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var viewModal = document.getElementById('view-field');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('view_field_model_type').textContent = button.getAttribute('data-model_type');
            document.getElementById('view_field_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_field_label').textContent = button.getAttribute('data-label');
            document.getElementById('view_field_type').textContent = button.getAttribute('data-type');

            // Handle select options (show as comma separated)
            var optionsLabel = document.getElementById('view_field_options_label');
            var optionsValue = document.getElementById('view_field_options');
            var type = button.getAttribute('data-type');
            var options = button.getAttribute('data-options');
            if(type === 'select' && options) {
                optionsLabel.style.display = '';
                optionsValue.style.display = '';
                optionsValue.textContent = options;
            } else {
                optionsLabel.style.display = 'none';
                optionsValue.style.display = 'none';
                optionsValue.textContent = '';
            }
        });
    }
});
</script>