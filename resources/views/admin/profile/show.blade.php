@extends('layouts.app-admin')
@section('title', 'Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Profile</h4>
                </div>
                <div class="card-body">
                    <h5 class="mb-3"><i class="ti ti-user text-primary me-1"></i> Basic Information</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Name:</div>
                        <div class="col-md-8">{{ $user->name }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Email:</div>
                        <div class="col-md-8">{{ $user->email }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Phone:</div>
                        <div class="col-md-8">{{ $user->phone }}</div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary me-2">
                            Edit Profile
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#passwordModal">
                            Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
@include('admin.profile.password-modal')
@endsection