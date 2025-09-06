<form wire:submit.prevent="store">
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @csrf

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>From <small class="text-muted">(Assets)</small></h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label">Account owner category *</label>
                                <select class="form-control"
                                    wire:change="changeSourceAccountableType($event.target.value)"
                                    wire:model="source_accountable_type" required>
                                    <option value="">Select an option</option>
                                    @foreach ($accountables as $item)
                                        <option value="{{ $item['class_id'] }}">{{ $item['alias'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" wire:model="date"
                                    value="{{ date('Y-m-d', strtotime(today())) }}"
                                    max="{{ date('Y-m-d', strtotime(today())) }}" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Account owner *</label>
                                <select class="form-control"
                                    wire:change="changeSourceAccountableId($event.target.value)"
                                    wire:model="source_accountable_id" required>
                                    <option value="">Select an option</option>
                                    @foreach ($source_accountable_data as $source_accountable_data_item)
                                        <option value="{{ $source_accountable_data_item->id }}">
                                            {{ $source_accountable_data_item->name ?? $source_accountable_data_item->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label">Source Account *</label>
                                <select class="form-control" wire:model="source_account_id" required>
                                    <option value="">Select an account</option>
                                    @if (!empty($sourceAccounts))
                                        @foreach ($sourceAccounts as $sourceAccount)
                                            <option value="{{ $sourceAccount->id }}">
                                                {{ $sourceAccount->title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Amount *</label>
                                <input type="text" class="form-control"
                                    wire:model="source_amount" required placeholder="Taka"
                                    pattern="^\d*(\.\d{0,2})?$"
                                    title="Please enter a valid number (e.g., 123.45)"
                                    wire:input="changeAmount($event.target.value)"
                                    oninput="validateInput(this);" maxlength="14">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Attachments <small class="text-muted">(jpg, jpeg, png, pdf)</small></label>
                                <input type="file" class="form-control"
                                    wire:model="attachments" multiple accept=".jpg,.jpeg,.png,.pdf">

                                <div wire:loading wire:target="attachments" class="text-info mt-2">
                                    Uploading files, please wait...
                                </div>

                                @error('attachments.*')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror

                                @if ($attachments)
                                    <ul class="mt-2">
                                        @foreach ($attachments as $file)
                                            <li>{{ $file->getClientOriginalName() }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>To <small class="text-muted">(Assets)</small></h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Account owner category *</label>
                                <select class="form-control"
                                    wire:change="changeDestinationAccountableType($event.target.value)"
                                    wire:model="destination_accountable_type" required>
                                    <option value="">Select an option</option>
                                    @foreach ($accountables as $item)
                                        <option value="{{ $item['class_id'] }}">{{ $item['alias'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Account owner *</label>
                                <select class="form-control"
                                    wire:change="changeDestinationAccountableId($event.target.value)"
                                    wire:model="destination_accountable_id" required>
                                    <option value="">Select an option</option>
                                    @foreach ($destination_accountable_data as $destination_accountable_data_item)
                                        <option value="{{ $destination_accountable_data_item->id }}">
                                            {{ $destination_accountable_data_item->name ?? $destination_accountable_data_item->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label">Destination *</label>
                                <select class="form-control" wire:model="destination_account_id" required>
                                    <option value="">Select an account</option>
                                    @if (!empty($destinationAccounts))
                                        @foreach ($destinationAccounts as $destinationAccount)
                                            <option value="{{ $destinationAccount->id }}">
                                                {{ $destinationAccount->title }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Amount</label>
                                <input type="text" class="form-control"
                                    wire:model="destination_amount" required placeholder="Taka"
                                    pattern="^\d*(\.\d{0,2})?$" readonly
                                    value="{{ $destination_amount }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Note</label>
                                <textarea class="form-control" wire:model.lazy="note" maxlength="500"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 text-end">
            <button 
                type="submit" 
                class="btn btn-primary" 
                id="submit"
                {{ $disabled ? 'disabled' : '' }}
                wire:loading.attr="disabled"
                wire:target="attachments"
            >
                Submit
            </button>
        </div>
    </div>
</form>

<script>
    function validateInput(input) {
        // Remove any characters that are not digits or decimal points
        input.value = input.value.replace(/[^0-9.]/g, '');

        // Limit to two decimal places
        const parts = input.value.split('.');
        if (parts.length > 2) {
            input.value = parts[0] + '.' + parts.slice(1).join('');
        }
        if (parts[1] && parts[1].length > 2) {
            input.value = parts[0] + '.' + parts[1].slice(0, 2);
        }

        // Limit total length to 14 characters
        if (input.value.length > 14) {
            input.value = input.value.slice(0, 14);
        }
    }
</script>