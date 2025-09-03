<!-- View Unit Modal -->
<div class="modal fade" id="viewUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Unit Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Unit Name:</dt>
                    <dd class="col-sm-8" id="view_unit_name"></dd>
                    <dt class="col-sm-4">Symbol:</dt>
                    <dd class="col-sm-8" id="view_unit_symbol"></dd>
                    <dt class="col-sm-4">Base</dt>
                    <dd class="col-sm-8" id="view_unit_parent"></dd>
                    <dt class="col-sm-4">Conversion Factor:</dt>
                    <dd class="col-sm-8" id="view_unit_conversion_factor"></dd>
                    <dt class="col-sm-4">Explanation:</dt>
                    <dd class="col-sm-8" id="view_unit_explanation"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /View Unit Modal -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    var viewModal = document.getElementById('viewUnitModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var name = button.getAttribute('data-name');
            var symbol = button.getAttribute('data-symbol');
            var parent = button.getAttribute('data-parent');
            var conversion_factor = button.getAttribute('data-conversion_factor');

            document.getElementById('view_unit_name').textContent = name;
            document.getElementById('view_unit_symbol').textContent = symbol;
            document.getElementById('view_unit_parent').textContent = parent;
            document.getElementById('view_unit_conversion_factor').textContent = conversion_factor;

            // Explanation: 1 [Unit] = X [Parent]
            var explanation = '-';
            if (parent && parent !== '-' && conversion_factor) {
                explanation = `1 ${name} = ${parseFloat(conversion_factor).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 6})} ${parent}`;
            }
            document.getElementById('view_unit_explanation').textContent = explanation;
        });
    }
});
</script>