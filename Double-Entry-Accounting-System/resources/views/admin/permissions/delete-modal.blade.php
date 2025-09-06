<!-- Delete Modal -->
<div class="modal fade" id="delete-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-5">
            <form id="deletePermissionForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center p-0">
                    <span class="rounded-circle d-inline-flex p-2 bg-danger-transparent mb-2">
                        <i class="ti ti-trash fs-24 text-danger"></i>
                    </span>
                    <h4 class="fs-20 text-gray-9 fw-bold mb-2 mt-1">Delete Permission</h4>
                    <p class="text-gray-6 mb-0 fs-16">Are you sure you want to delete this permission?</p>
                    <div class="d-flex justify-content-center mt-3">
                        <button type="button" class="btn me-2 btn-secondary fs-13 fw-medium p-2 px-3 shadow-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger fs-13 fw-medium p-2 px-3">Yes Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Delete Modal --> 