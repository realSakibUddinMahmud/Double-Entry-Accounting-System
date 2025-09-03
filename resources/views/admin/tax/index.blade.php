@extends('layouts.app-admin')
@section('title', 'Tax')

@section('content')
@can('tax-view')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Taxes</h4>
            <h6>Manage your Taxes</h6>
        </div>
    </div>
    @can('tax-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-tax">
            <i class="ti ti-circle-plus me-1"></i>Add Tax
        </a>
    </div>
    @endcan
</div>
<!-- /tax list -->
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
                        <th>Rate (%)</th>
                        <th>Status</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $tax)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $tax->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td class="text-gray-9">{{ $tax->name }}</td>
                            <td>{{ $tax->rate }}</td>
                            <td>
                                @if($tax->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('tax-view')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-tax"
                                       data-id="{{ $tax->id }}"
                                       data-name="{{ $tax->name }}"
                                       data-rate="{{ $tax->rate }}"
                                       data-status="{{ $tax->status }}"
                                       title="View Tax">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('tax-edit')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit-tax"
                                       data-id="{{ $tax->id }}"
                                       data-name="{{ $tax->name }}"
                                       data-rate="{{ $tax->rate }}"
                                       data-status="{{ $tax->status }}"
                                       title="Edit Tax">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('tax-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-tax"
                                       data-id="{{ $tax->id }}"
                                       title="Delete Tax">
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
<!-- /tax list -->

<!-- Include Modals Based on Permissions -->
@can('tax-create')
@include('admin.tax.create-modal')
@endcan

@can('tax-edit')
@include('admin.tax.edit-modal')
@endcan

@can('tax-delete')
@include('admin.tax.delete-modal')
@endcan

@can('tax-view')
@include('admin.tax.view-modal')
@endcan

@else
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <h4>Access Denied</h4>
            <p>You don't have permission to view taxes.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('edit-tax');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var rate = button.getAttribute('data-rate');
            var status = button.getAttribute('data-status');

            document.getElementById('editTaxForm').action = "{{ url('taxes') }}/" + id;
            document.getElementById('edit_tax_name').value = name;
            document.getElementById('edit_tax_rate').value = rate;
            document.getElementById('edit_tax_status').value = status;
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('delete-tax');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteTaxForm').action = "{{ url('taxes') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('view-tax');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('view_tax_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_tax_rate').textContent = button.getAttribute('data-rate');
            document.getElementById('view_tax_status').textContent = button.getAttribute('data-status') == 1 ? 'Active' : 'Inactive';
        });
    }
});
</script>





