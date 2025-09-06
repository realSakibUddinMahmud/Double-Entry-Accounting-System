@extends('layouts.app-admin')
@section('title', 'Roles')

@section('content')
@can('role-view')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Roles</h4>
            <h6>Manage your Roles</h6>
        </div>
    </div>
    @can('role-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-role">
            <i class="ti ti-circle-plus me-1"></i>Add Role
        </a>
    </div>
    @endcan
</div>

<!-- /role list -->
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
                        <th>Permissions</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $role->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->guard_name }}</td>
                            <td>
                                @if($role->permissions->count() > 0)
                                    {{ $role->permissions->count() }} permissions
                                @else
                                    No permissions
                                @endif
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('role-view')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-role"
                                       data-id="{{ $role->id }}"
                                       data-name="{{ $role->name }}"
                                       data-guard_name="{{ $role->guard_name }}"
                                       data-permissions="{{ $role->permissions->pluck('name')->implode(', ') }}"
                                       title="View Role">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('role-edit')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit-role"
                                       data-id="{{ $role->id }}"
                                       data-name="{{ $role->name }}"
                                       data-guard_name="{{ $role->guard_name }}"
                                       data-permissions="{{ $role->permissions->pluck('id')->toJson() }}"
                                       title="Edit Role">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('role-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-modal"
                                       data-id="{{ $role->id }}"
                                       title="Delete Role">
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
<!-- /role list -->

<!-- Include Modals Based on Permissions -->
@can('role-create')
@include('admin.roles.create-modal')
@endcan

@can('role-edit')
@include('admin.roles.edit-modal')
@endcan

@can('role-delete')
@include('admin.roles.delete-modal')
@endcan

@can('role-view')
@include('admin.roles.view-modal')
@endcan

@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view roles.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('edit-role');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var guard_name = button.getAttribute('data-guard_name');
            var permissions = JSON.parse(button.getAttribute('data-permissions'));

            document.getElementById('editRoleForm').action = "{{ url('roles') }}/" + id;
            document.getElementById('edit_role_name').value = name;
            document.getElementById('edit_role_guard_name').value = guard_name;

            // Check the checkboxes for permissions this role has
            document.querySelectorAll('#edit-role input[name="permissions[]"]').forEach(function(checkbox) {
                checkbox.checked = permissions.includes(parseInt(checkbox.value));
            });
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('delete-modal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteRoleForm').action = "{{ url('roles') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('view-role');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            
            document.getElementById('view_role_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_role_guard_name').textContent = button.getAttribute('data-guard_name');
            
            var permissionsList = document.getElementById('view_role_permissions');
            permissionsList.innerHTML = '';
            var permissions = button.getAttribute('data-permissions');
            if (permissions) {
                permissions.split(', ').forEach(function(permission) {
                    var li = document.createElement('li');
                    li.textContent = permission;
                    permissionsList.appendChild(li);
                });
            } else {
                var li = document.createElement('li');
                li.textContent = 'No permissions assigned';
                permissionsList.appendChild(li);
            }
        });
    }
});
</script>