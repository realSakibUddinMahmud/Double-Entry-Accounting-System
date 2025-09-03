@extends('layouts.app-admin')
@section('title', 'Company Users')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Users of {{ $company->name }}</h4>
        <h6>Manage users assigned to this company</h6>
    </div>
    <div class="page-btn">
        <a href="{{ route('companies.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>Back to Companies
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                @if($user->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : 'N/A' }}
                            </td>
                            <td>
                                <div class="d-flex">
                                    <a href="#" class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteUserModal"
                                    data-user-id="{{ $user->id }}"
                                    data-company-id="{{ $company->id }}">
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Note:Nothing will be add here --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.company.delete-user-modal')

@endsection

@section('scripts')  
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete Modal
    var deleteModal = document.getElementById('deleteUserModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var companyId = button.getAttribute('data-company-id');
            
            document.getElementById('deleteUserForm').action = 
                "{{ url('companies') }}/" + companyId + "/users/" + userId;
        });
    }
});
</script>
@endsection
