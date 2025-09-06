<form method="GET" class="row" target="_self">
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
    {{-- <div class="col-sm-3">
        <label>For</label>
        @php
            $activeGasStations = App\Models\GasStation::where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();
        @endphp
        <select class="form-control form-control-sm" name="gs_id" id='gs_id'>
            @if (request('gs_id'))
                @php
                    $selected_gs = App\Models\GasStation::where('status', 'ACTIVE')->find(request('gs_id'));
                @endphp
                @if ($selected_gs)
                    <option value="{{ $selected_gs->id }}">{{ $selected_gs->name }}</option>
                @endif
            @endif
            <option value="NATIVE">Companies native account</option>
            <option value="">All Gas Stations</option>
            @foreach ($activeGasStations as $active_gs)
                @if (request('gs_id') && request('gs_id') != $active_gs->id)
                    <option value="{{ $active_gs->id }}">{{ $active_gs->name }}</option>
                @else
                    <option value="{{ $active_gs->id }}">{{ $active_gs->name }}</option>
                @endif
            @endforeach
        </select>
    </div> --}}
    <div class="col-sm-2 d-flex-sm">
        <label>&nbsp;</label><br>
        <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">View</button>
        <button type="submit" class="btn btn-dark btn-md d-inline-flex align-items-center" name="download"
            value="YES">PDF</button>
    </div>
</form>
@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- jQuery UI (with datepicker) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <script>
        function initDatepickers() {
            $('.datepicker').datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
                maxDate: new Date() // Restrict to today or earlier
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            initDatepickers();

            Livewire.hook('message.processed', (message, component) => {
                initDatepickers();
            });
        });
    </script>
@endpush
