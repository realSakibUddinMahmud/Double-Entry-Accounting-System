@extends('layouts.app-admin')

@section('title', 'Settings')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Settings</h4>
        <h6>Manage system settings</h6>
    </div>
</div>


<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            
            <div class="row mb-3">
                <div class="col-12">
                    <h6 class="mb-3 text-primary">Journal Display Settings</h6>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="show_journal_in_sale_invoice" id="show_journal_in_sale_invoice" 
                            {{ $settings['show_journal_in_sale_invoice'] ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_journal_in_sale_invoice">
                            Show Journal in Sale Invoice
                        </label>
                        <small class="form-text text-muted d-block">
                            Display journal entries in sale invoice documents
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="show_journal_in_purchase_bill" id="show_journal_in_purchase_bill" 
                            {{ $settings['show_journal_in_purchase_bill'] ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_journal_in_purchase_bill">
                            Show Journal in Purchase Bill
                        </label>
                        <small class="form-text text-muted d-block">
                            Display journal entries in purchase bill documents
                        </small>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
