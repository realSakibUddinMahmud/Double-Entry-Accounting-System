@extends('layouts.app-admin')
@section('title', 'Add Company')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Add Company</h4>
        <h6>Create a new company</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('companies.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Company List
        </a>
    </div>
</div>
@if(session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="card">
    <div class="card-body">
        <form action="{{ route('companies.store') }}" method="POST" id="createCompanyForm" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Region</label>
                    <input type="text" name="region" class="form-control">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" name="contact_no" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Office Address</label>
                    <textarea name="office_address" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Database Name <span class="text-danger">*</span></label>
                    <input type="text" name="db_name" class="form-control" placeholder="Example: hilinkz_inventory_yourcompany" required>
                    <small class="text-muted">Unique database name for this company</small>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Full Domain Name <span class="text-danger">*</span></label>
                    <input type="text" name="domain_name" class="form-control" placeholder="Example: new_company.ryofin.com" required>
                    <small class="text-muted">Choose domain name for this company</small>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Company Images</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    <small class="text-muted">You can select multiple images</small>
                </div>
            </div>
            <div class="modal-footer px-0">
                <a href="{{ route('companies.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Company</button>
            </div>
        </form>
    </div>
</div>
@endsection