{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/unit/index.blade.php --}}

@extends('layouts.app-admin')
@section('title', 'Unit')

@section('content')
@can('unit-view')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Units</h4>
            <h6>Manage your Units</h6>
        </div>
    </div>
    @can('unit-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUnitModal">
            <i class="ti ti-circle-plus me-1"></i>Add Unit
        </a>
    </div>
    @endcan
</div>
<!-- /unit list -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Search & Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('units.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search Unit Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search by unit name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Parent Unit</label>
                <select name="parent_id" class="form-select">
                    <option value="">All Units</option>
                    <option value="none" {{ request('parent_id') === 'none' ? 'selected' : '' }}>No Parent (Base)</option>
                    @foreach($parentUnits as $parentUnit)
                        <option value="{{ $parentUnit->id }}" {{ request('parent_id') == $parentUnit->id ? 'selected' : '' }}>
                            {{ $parentUnit->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1"></i>Search
                </button>
                <a href="{{ route('units.index') }}" class="btn btn-secondary">
                    <i class="ti ti-refresh me-1"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="card-title mb-0">Unit List</h5>
        <div class="ms-auto">
            <span class="text-muted">Total: {{ $units->total() }} units</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Base</th>
                        <th>Conversion Factor</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $unit->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td class="text-gray-9">
                                {{ $unit->name }}
                            </td>
                            <td>
                                {{ $unit->symbol ?? '-' }}
                            </td>
                            <td>
                                {{ optional($unit->parent)->name ?? '-' }}
                            </td>
                            <td>
                                {{ $unit->conversion_factor ?? '-' }}
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('unit-view')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#viewUnitModal"
                                       data-id="{{ $unit->id }}"
                                       data-name="{{ $unit->name }}"
                                       data-symbol="{{ $unit->symbol }}"
                                       data-parent_id="{{ $unit->parent_id }}"
                                       data-parent="{{ optional($unit->parent)->name }}"
                                       data-conversion_factor="{{ $unit->conversion_factor }}"
                                       data-created_at="{{ $unit->created_at }}"
                                       title="View Unit">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('unit-edit')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#editUnitModal"
                                       data-id="{{ $unit->id }}"
                                       data-name="{{ $unit->name }}"
                                       data-symbol="{{ $unit->symbol }}"
                                       data-parent_id="{{ $unit->parent_id }}"
                                       data-conversion_factor="{{ $unit->conversion_factor }}"
                                       title="Edit Unit">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('unit-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#deleteUnitModal"
                                       data-id="{{ $unit->id }}"
                                       title="Delete Unit">
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
        
        <!-- Pagination -->
        <x-admin.pagination :paginator="$units" info-text="units" />
    </div>
</div>
<!-- /unit list -->

<!-- Include Modals Based on Permissions -->
@can('unit-create')
@include('admin.unit.create-modal')
@endcan

@can('unit-edit')
@include('admin.unit.edit-modal')
@endcan

@can('unit-delete')
@include('admin.unit.delete-modal')
@endcan

@can('unit-view')
@include('admin.unit.view-modal')
@endcan

@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view units.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('editUnitModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var symbol = button.getAttribute('data-symbol');
            var parent_id = button.getAttribute('data-parent_id');
            var conversion_factor = button.getAttribute('data-conversion_factor');

            document.getElementById('editUnitForm').action = "{{ url('units') }}/" + id;
            document.getElementById('edit_unit_name').value = name;
            document.getElementById('edit_unit_symbol').value = symbol;
            document.getElementById('edit_unit_parent_id').value = parent_id;
            document.getElementById('edit_unit_conversion_factor').value = conversion_factor;
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('deleteUnitModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteUnitForm').action = "{{ url('units') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('viewUnitModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('view_unit_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_unit_symbol').textContent = button.getAttribute('data-symbol');
            document.getElementById('view_unit_parent').textContent = button.getAttribute('data-parent');
            document.getElementById('view_unit_conversion_factor').textContent = button.getAttribute('data-conversion_factor');
            document.getElementById('view_unit_created_at').textContent = button.getAttribute('data-created_at');
        });
    }
});
</script>





