<!-- View Company Modal -->
<div class="modal fade" id="viewCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Company Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name:</dt>
                    <dd class="col-sm-8" id="view_company_name"></dd>
                    <dt class="col-sm-4">Region:</dt>
                    <dd class="col-sm-8" id="view_company_region"></dd>
                    <dt class="col-sm-4">Contact No:</dt>
                    <dd class="col-sm-8" id="view_company_contact_no"></dd>
                    <dt class="col-sm-4">Email:</dt>
                    <dd class="col-sm-8" id="view_company_email"></dd>
                    <dt class="col-sm-4">Office Address:</dt>
                    <dd class="col-sm-8" id="view_company_office_address"></dd>
                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8" id="view_company_status"></dd>
                    {{-- <dt class="col-sm-4">Images:</dt>
                    <dd class="col-sm-8" id="view_company_images">
                        <!-- Images will be loaded here -->
                    </dd> --}}
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /View Company Modal -->