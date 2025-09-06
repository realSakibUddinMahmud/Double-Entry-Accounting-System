{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/packages/Hilinkz/DEAccounting/resources/views/livewire/account/create.blade.php --}}
<form wire:submit.prevent="store">
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    @csrf

    <div class="card">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="account_no" class="form-label">Account No (Optional)</label>
                    <input type="text" class="form-control" wire:model="account_no" placeholder="Ex: 10011">
                    @error('account_no')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Account Title *</label>
                    <input type="text" class="form-control" wire:model="title" placeholder="Ex: Roket, Nagad, DBBL" required>
                    @error('title')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Financial Type *</label>
                    <select class="form-control" wire:model="root_type" required>
                        <option value="">----- Select Option -----</option>
                        @foreach ($rootTypes as $rootType)
                            <option value="{{ $rootType['id'] }}">{{ $rootType['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Account Type</label>
                    <select class="form-control"
                        wire:change="changeAccountType($event.target.value)"
                        wire:model="account_type_id">
                        <option value="">----- Select Option -----</option>
                        @foreach ($accountTypes as $accountType)
                            <option value="{{ $accountType->id }}">{{ $accountType->title }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($this->selected_account_type == 'Bank')
                    <div class="form-group mb-3">
                        <label class="form-label">A/C Holder Name *</label>
                        <input type="text" class="form-control" wire:model="ac_holder_name" placeholder="Enter bank name">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Bank A/C No *</label>
                        <input type="text" class="form-control" wire:model="bank_ac_no" placeholder="Enter bank a/c no">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Bank Name *</label>
                        <select class="form-control" wire:model="bank_id" required>
                            <option value="">----- Select Option -----</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Branch *</label>
                        <input type="text" class="form-control" wire:model="branch" placeholder="Enter branch name" required>
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label">Account For *</label>
                    <select class="form-control"
                        wire:change="changeAccountableType($event.target.value)"
                        wire:model="accountable_type" required>
                        <option value="">----- Select Option -----</option>
                        @foreach ($accountables as $item)
                            <option value="{{ $item['class_id'] }}">{{ $item['alias'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Belong To *</label>
                    <select multiple="multiple" class="form-control" wire:model="accountable_id" required>
                        <option value="">----- Select Option -----</option>
                        @foreach ($accountable_data as $accountable_data_item)
                            <option value="{{ $accountable_data_item->id }}">
                                {{ $accountable_data_item->name ?? $accountable_data_item->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Parent Account</label>
                    <select class="form-control" wire:model="parent_title">
                        <option value="">----- Select Option -----</option>
                        @foreach ($accounts as $accountTitle)
                            <option value="{{ $accountTitle }}">{{ $accountTitle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary" id="submit">Submit</button>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('livewire:load', function() {
        Livewire.on('logToConsole', function(data) {
            console.log('Livewire Debug Info:', data);
        });
    });
</script>