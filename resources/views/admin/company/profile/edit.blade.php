@extends('layouts.app-admin')
@section('title', 'Edit Company Profile')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Company Profile</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('company.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    @if ($logo)
                                        <img src="{{ asset('storage/' . $logo->path) }}" id="logo-preview"
                                            alt="Company Logo"
                                            style="height: 100px; width: 100px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('admin/no-image.png') }}" id="logo-preview" alt="No Logo"
                                            style="height: 100px; width: 100px; object-fit: cover; border-radius: 50%;">
                                    @endif
                                </div>
                            </div>

                            {{-- <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <label for="logo" class="btn btn-secondary">
                                        <i class="ti ti-upload me-1"></i> Change Logo
                                        <input type="file" id="logo" name="logo" class="d-none" accept="image/*">
                                    </label>
                                    @error('logo')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div> --}}

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $company->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_no" class="form-control"
                                        value="{{ old('contact_no', $company->contact_no) }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $company->email) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Region</label>
                                    <input type="text" name="region" class="form-control"
                                        value="{{ old('region', $company->region) }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Office Address</label>
                                    <textarea name="office_address" class="form-control" rows="3">{{ old('office_address', $company->office_address) }}</textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Logo (Image)</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('company.profile') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logo preview
            const logoInput = document.getElementById('logo');
            const logoPreview = document.getElementById('logo-preview');

            logoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        logoPreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endsection
