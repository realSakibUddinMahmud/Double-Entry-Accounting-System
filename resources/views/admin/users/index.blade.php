@extends('layouts.app-admin')
@section('title', 'Users')

@section('content')
@can('user-view')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <span>Users</span>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone No.</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th style="text-align: center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user as $u)
                            <tr>
                                <td>{{ $u->id }}</td>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->phone }}</td>
                                {{-- <td>{{ $u->tenant_id }}</td> --}}
                                <td>{{ $u->company_name ?? 'No Company' }}</td>
                                <td>
                                    <span class="badge badge-{{ $u->status ? 'success' : 'danger' }}">
                                        {{ $u->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td style="text-align: center">
                                    @can('user-role-assign')
                                    <a href="{{ route('admin.user-roles.edit', $u->id) }}" class="btn btn-primary btn-sm">Assign Roles</a>
                                    @endcan

                                    @can('user-status-toggle')
                                    <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $u->status ? 'btn-warning' : 'btn-success' }}">
                                            {{ $u->status ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view users.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan
@endsection
