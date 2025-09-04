<!-- View Brand Modal -->
<div class="modal fade" id="view-brand" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Brand Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="view_brand_logo" src="{{ asset('admin/no-image.png') }}" alt="Logo" style="height:200px; width:200px; object-fit:cover; border-radius:50%;">
                    <div id="view_brand_logo_log" class="text-danger mt-2" style="display:none; font-size: 12px;"></div>
                </div>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Brand Name:</dt>
                    <dd class="col-sm-8" id="view_brand_name"></dd>
                    <dt class="col-sm-4">Slug:</dt>
                    <dd class="col-sm-8" id="view_brand_slug"></dd>
                    <dt class="col-sm-4">Description:</dt>
                    <dd class="col-sm-8" id="view_brand_description"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /View Brand Modal -->