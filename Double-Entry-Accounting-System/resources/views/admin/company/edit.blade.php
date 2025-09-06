@extends('layouts.app-admin')
@section('title', 'Edit Company')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Company</h4>
        <h6>Update company details</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('companies.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Company List
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Region</label>
                    <input type="text" name="region" class="form-control" value="{{ old('region', $company->region) }}">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $company->contact_no) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Office Address</label>
                    <textarea name="office_address" class="form-control" rows="2">{{ old('office_address', $company->office_address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Database Name</label>
                    <input type="text" class="form-control" value="{{ old('db_name', $company->db_name) }}" readonly>
                    <small class="text-muted">Database name cannot be changed after creation</small>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="ACTIVE" {{ old('status', $company->status) === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ old('status', $company->status) === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Full Domain Name</label>
                    <input type="text" class="form-control bg-light" 
                        value="{{ $tenant->domain ?? 'N/A' }}" readonly>
                    <small class="text-muted">Domain name cannot be changed after creation</small>
                </div>
            </div>
            
            {{-- <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Company Images</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    <div class="mt-2">
                        @if($company->images->count())
                            @foreach($company->images as $image)
                                <img src="{{ asset('storage/'.$image->path) }}" alt="Company Image" style="max-width:60px;max-height:60px;" class="me-1 mb-1 border rounded">
                            @endforeach
                        @endif
                    </div>
                </div>
            </div> --}}
            
            <div class="modal-footer px-0">
                <a href="{{ route('companies.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection