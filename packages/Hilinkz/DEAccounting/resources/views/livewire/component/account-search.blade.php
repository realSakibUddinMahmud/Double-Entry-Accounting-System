{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/packages/Hilinkz/DEAccounting/resources/views/livewire/component/account-search.blade.php --}}
<form method="GET" class="row align-items-end g-2 mb-3">
    <div class="col-md-2">
        <label class="form-label" for="receivable">Belong To</label>
        <select class="form-control form-control-sm"
                wire:model="accountable_type"
                wire:change="changeAccountableType($event.target.value)"
                name="accountable_type">
            <option value="">Select an option</option>
            @foreach($accountables as $item)
                <option value="{{ $item['class_id'] }}">
                    {{ $item['alias'] }}
                </option>
            @endforeach
        </select>
    </div>

    @if($selected_accountable_type_alias)
        <div class="col-md-3">
            <label class="form-label">Select {{ $selected_accountable_type_alias }}</label>
            <select class="form-control form-control-sm"
                    wire:model="accountable_id"
                    name="accountable_id">
                <option value="">Select an option</option>
                @foreach($accountable_data as $accountable_data_item)
                    <option value="{{ $accountable_data_item->id }}">
                        {{ $accountable_data_item->name ?? $accountable_data_item->title }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="col-md-2">
        <label class="form-label" for="accountable_types">Account Type</label>
        <select class="form-control form-control-sm"
                wire:model="account_type_id"
                name="account_type_id">
            <option value="">Select an option</option>
            @foreach($accountTypes as $type)
                <option value="{{ $type->id }}">
                    {{ $type->title }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="accountable_types">Account No / Title</label>
        <input type="text" class="form-control form-control-sm" name="title_ac_no" wire:model="title_ac_no" placeholder="Search by Title or Account No...">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-sm w-100" onclick="myPreloader()">
            <i class="ti ti-search me-1"></i>Search
        </button>
    </div>
</form>