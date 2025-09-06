<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Edit Unit</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="editUnitForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" id="edit_unit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Symbol <span class="text-muted">(optional)</span></label>
                        <input type="text" name="symbol" class="form-control" id="edit_unit_symbol">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Base Unit</label>
                        <select name="parent_id" class="form-control" id="edit_unit_parent_id">
                            <option value="">-- None --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Conversion Factor</label>
                        <input type="number" step="0.01" name="conversion_factor" class="form-control" id="edit_unit_conversion_factor" placeholder="e.g. 1, 0.01">
                        <div class="form-text">
                            How many of base unit equals this unit?<br>
                            <div class="alert alert-primary py-2 px-3 my-2" style="font-weight:bold; font-size:1.1em; border:2px solid #0d6efd;">
                                <span class="text-primary">
                                    <strong>Explanation:</strong>
                                    1 <span id="edit_example_unit_name">[Unit]</span> = 
                                    <span id="edit_example_conversion_factor">X.00</span> 
                                    <span id="edit_example_base_unit">[Base Unit]</span>
                                </span>
                            </div>
                            <span class="text-success" style="font-weight:bold; font-size:1.05em;">
                                <strong>Example:</strong> 1 Dozen = 12.00 Piece
                            </span>
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
<!-- /Edit Unit Modal -->
<script>
    var editModal = document.getElementById('editUnitModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var name = button.getAttribute('data-name');
            var symbol = button.getAttribute('data-symbol');
            var parent_id = button.getAttribute('data-parent_id');
            var conversion_factor = button.getAttribute('data-conversion_factor');

            document.getElementById('edit_unit_name').value = name;
            document.getElementById('edit_unit_symbol').value = symbol;
            document.getElementById('edit_unit_parent_id').value = parent_id;
            document.getElementById('edit_unit_conversion_factor').value = conversion_factor;

            // Update explanation and example
            updateEditExample();
        });

        // Update example/explanation live
        function updateEditExample() {
            const unitName = document.getElementById('edit_unit_name').value || '[Unit]';
            const baseUnitSelect = document.getElementById('edit_unit_parent_id');
            const baseUnit = baseUnitSelect.options[baseUnitSelect.selectedIndex].text !== '-- None --'
                ? baseUnitSelect.options[baseUnitSelect.selectedIndex].text
                : '[Base Unit]';
            let val = document.getElementById('edit_unit_conversion_factor').value;
            let conv = (val && !isNaN(val)) ? parseFloat(val).toFixed(2) : 'X.00';
            document.getElementById('edit_example_unit_name').textContent = unitName;
            document.getElementById('edit_example_base_unit').textContent = baseUnit;
            document.getElementById('edit_example_conversion_factor').textContent = conv;
        }

        document.getElementById('edit_unit_name').addEventListener('input', updateEditExample);
        document.getElementById('edit_unit_parent_id').addEventListener('change', updateEditExample);
        document.getElementById('edit_unit_conversion_factor').addEventListener('input', updateEditExample);
    }
</script>