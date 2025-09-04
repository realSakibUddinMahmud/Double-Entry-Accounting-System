<!-- View Role Modal -->
<div class="modal fade" id="view-role" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Role Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name:</dt>
                    <dd class="col-sm-8" id="view_role_name"></dd>
                    <dt class="col-sm-4">Guard Name:</dt>
                    <dd class="col-sm-8" id="view_role_guard_name"></dd>
                    <dt class="col-sm-4">Permissions:</dt>
                    <dd class="col-sm-8">
                        <ul id="view_role_permissions" class="mb-0"></ul>
                    </dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /View Role Modal -->