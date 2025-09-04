@extends('layouts.app-admin')
@section('title', 'Company Profile')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Company Profile</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            @if ($logo)
                                <img src="{{ asset('storage/' . $logo->path) }}" alt="Company Logo"
                                    style="height: 100px; width: 100px; object-fit: cover;">
                            @else
                                <img src="{{ asset('admin/no-image.png') }}" alt="No Logo"
                                    style="height: 100px; width: 100px; object-fit: cover; border-radius: 50%;">
                            @endif
                        </div>

                        <h5 class="mb-3"><i class="ti ti-building text-primary me-1"></i> Company Information</h5>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Company Name:</div>
                            <div class="col-md-8">{{ $company->name }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Contact Number:</div>
                            <div class="col-md-8">{{ $company->contact_no }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Email:</div>
                            <div class="col-md-8">{{ $company->email ?? 'N/A' }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Office Address:</div>
                            <div class="col-md-8">{{ $company->office_address ?? 'N/A' }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Region:</div>
                            <div class="col-md-8">{{ $company->region ?? 'N/A' }}</div>
                        </div>

                    @can('company-profile-show')
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('company.profile.edit') }}" class="btn btn-primary">
                                Edit Profile
                            </a>
                        </div>
                    @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
