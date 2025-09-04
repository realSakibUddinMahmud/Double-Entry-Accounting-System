{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/permissions/index.blade.php --}}

@extends('layouts.app-admin')
@section('title', 'Permissions')

@section('content')
@can('permission-view')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Permissions</h4>
            <h6>Manage your Permissions</h6>
        </div>
    </div>
    @can('permission-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-permission">
            <i class="ti ti-circle-plus me-1"></i>Add Permission
        </a>
    </div>
    @endcan
</div>

<!-- /permission list -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <div class="search-set">
            <div class="search-input">
                <span class="btn-searchset">
                    <i class="ti ti-search fs-14 feather-search"></i>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>Name</th>
                        <th>Guard</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $permission->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->guard_name }}</td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('permission-view')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-permission"
                                       data-id="{{ $permission->id }}"
                                       data-name="{{ $permission->name }}"
                                       data-guard_name="{{ $permission->guard_name }}"
                                       title="View Permission">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('permission-edit')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit-permission"
                                       data-id="{{ $permission->id }}"
                                       data-name="{{ $permission->name }}"
                                       data-guard_name="{{ $permission->guard_name }}"
                                       title="Edit Permission">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('permission-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-modal"
                                       data-id="{{ $permission->id }}"
                                       title="Delete Permission">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                    @endcan
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
<!-- /permission list -->

<!-- Include Modals Based on Permissions -->
@can('permission-create')
@include('admin.permissions.create-modal')
@endcan

@can('permission-edit')
@include('admin.permissions.edit-modal')
@endcan

@can('permission-delete')
@include('admin.permissions.delete-modal')
@endcan

@can('permission-view')
@include('admin.permissions.view-modal')
@endcan

@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view permissions.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('edit-permission');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var guard_name = button.getAttribute('data-guard_name');

            document.getElementById('editPermissionForm').action = "{{ url('permissions') }}/" + id;
            document.getElementById('edit_permission_name').value = name;
            document.getElementById('edit_permission_guard_name').value = guard_name;
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('delete-modal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deletePermissionForm').action = "{{ url('permissions') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('view-permission');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            
            document.getElementById('view_permission_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_permission_guard_name').textContent = button.getAttribute('data-guard_name');
        });
    }
});
</script>