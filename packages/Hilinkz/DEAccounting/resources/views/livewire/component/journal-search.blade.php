<form id="searchForm" method="GET" class="row" target="_self">
    <div class="col-sm-2">
        <label>From</label>
        <input type="text" class="form-control form-control-sm datepicker" name="start_date"
            value="{{ request('start_date') ?? date('d-m-Y', strtotime(today())) }}" data-date-format="dd-mm-yy"
            max="{{ date('d-m-Y', strtotime(today())) }}">
    </div>
    <div class="col-sm-2">
        <label>To</label>
        <input type="text" class="form-control form-control-sm datepicker" name="end_date"
            value="{{ request('end_date') ?? date('d-m-Y', strtotime(today())) }}" data-date-format="dd-mm-yy"
            max="{{ date('d-m-Y', strtotime(today())) }}">
    </div>

    <div class="col-sm-2">
        <label for="receivable">Belong To</label>
        <select class="form-control form-control-sm" wire:model="accountable_type"
            wire:change="changeAccountableType($event.target.value)" name="accountable_type">
            <option value="">Select an option</option>
            @foreach ($accountables as $item)
                <option value="{{ $item['class_id'] }}">
                    {{ $item['alias'] }}
                </option>
            @endforeach
        </select>
    </div>

    @if ($selected_accountable_type_alias)
        <div class="col-sm-4">
            <label>Select {{ $selected_accountable_type_alias }}</label>
            <select class="form-control form-control-sm" wire:model="accountable_id" name="accountable_id">
                <option value="">Select an option</option>
                @foreach ($accountable_data as $accountable_data_item)
                    <option value="{{ $accountable_data_item->id }}">
                        {{ $accountable_data_item->name ?? $accountable_data_item->title }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="col-sm-2 d-flex-sm">
        <label>&nbsp;</label><br>
        <button type="submit" id="viewBtn"
            class="btn btn-primary btn-md d-inline-flex align-items-center">View</button>
        <button type="submit" id="pdfBtn" class="btn btn-dark btn-md d-inline-flex align-items-center"
            name="download" value="YES">PDF</button>
    </div>
</form>
@push('scripts')
    {{-- <script>
    function initDatepickers() {
        $('.datepicker').datepicker({
            dateFormat: 'dd-mm-yy'
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        initDatepickers();

        Livewire.hook('message.processed', (message, component) => {
            initDatepickers();
        });
    });
</script> --}}
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery UI (with datepicker) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize datepickers with proper dd/mm/yyyy format
            $('.datepicker').datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
                maxDate: new Date() // Restrict to today or earlier
            });

            // Ensure Livewire doesn't break the datepickers
            Livewire.hook('message.processed', () => {
                $('.datepicker').datepicker('refresh');
            });
        });
    </script>
@endpush
