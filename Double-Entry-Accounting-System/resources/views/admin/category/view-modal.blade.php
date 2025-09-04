<!-- View Category Modal -->
<div class="modal fade" id="view-category" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Category Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Category Path:</dt>
                    <dd class="col-sm-8" id="view_category_path"></dd>
                    <dt class="col-sm-4">Category Name:</dt>
                    <dd class="col-sm-8" id="view_category_name"></dd>
                    <dt class="col-sm-4">Parent:</dt>
                    <dd class="col-sm-8" id="view_category_parent"></dd>
                    <dt class="col-sm-4">Created At:</dt>
                    <dd class="col-sm-8" id="view_category_created_at"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /View Category Modal -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    var viewModal = document.getElementById('view-category');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('view_category_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_category_parent').textContent = button.getAttribute('data-parent');
            document.getElementById('view_category_created_at').textContent = button.getAttribute('data-created_at');
            // Show category path (parent > child > ...)
            var path = button.getAttribute('data-path');
            document.getElementById('view_category_path').textContent = path ? path : '-';
        });
    }
});
</script>