@extends('layouts.app-admin')
@section('title', 'Brand')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Brands</h4>
            <h6>Manage your Brand</h6>
        </div>
    </div>
    @can('brand-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-brand">
            <i class="ti ti-circle-plus me-1"></i>Add Brand
        </a>
    </div>
    @endcan
</div>
<!-- /brand list -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Search & Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('brands.index') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Search Brand Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search by brand name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1"></i>Search
                </button>
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">
                    <i class="ti ti-refresh me-1"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="card-title mb-0">Brand List</h5>
        <div class="ms-auto">
            <span class="text-muted">Total: {{ $brands->total() }} brands</span>
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
                        <th>Logo</th>
                        <th>Brand</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $brand->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>
                                @if($brand->logo)
                                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="Logo" style="height:32px; width:32px; object-fit:cover; border-radius:50%;">
                                @else
                                    <img src="{{ asset('admin/no-image.png') }}" alt="No Image" style="height:32px; width:32px; object-fit:cover; border-radius:50%;">
                                @endif
                            </td>
                            <td class="text-gray-9">{{ $brand->name }}</td>
                            <td>{{ $brand->slug }}</td>
                            <td>{{ $brand->description }}</td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('brand-view')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-brand"
                                       data-id="{{ $brand->id }}"
                                       data-name="{{ $brand->name }}"
                                       data-slug="{{ $brand->slug }}"
                                       data-logo="{{ $brand->logo }}"
                                       data-description="{{ $brand->description }}">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    @can('brand-edit')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit-brand"
                                       data-id="{{ $brand->id }}"
                                       data-name="{{ $brand->name }}"
                                       data-slug="{{ $brand->slug }}"
                                       data-logo="{{ $brand->logo }}"
                                       data-description="{{ $brand->description }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    @can('brand-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-modal"
                                       data-id="{{ $brand->id }}">
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
        <x-admin.pagination :paginator="$brands" info-text="brands" />
    </div>
</div>
<!-- /brand list -->

@can('brand-create')
@include('admin.brand.create-modal')
@endcan
@can('brand-edit')
@include('admin.brand.edit-modal')
@endcan
@can('brand-delete')
@include('admin.brand.delete-modal')
@endcan
@can('brand-view')
@include('admin.brand.view-modal')
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('edit-brand');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var slug = button.getAttribute('data-slug');
            var logo = button.getAttribute('data-logo');
            var description = button.getAttribute('data-description');
            var status = button.getAttribute('data-status');

            document.getElementById('editBrandForm').action = "{{ url('brands') }}/" + id;

            document.getElementById('edit_brand_name').value = name;
            document.getElementById('edit_brand_slug').value = slug;
            document.getElementById('edit_brand_logo').value = logo;
            document.getElementById('edit_brand_description').value = description;
            document.getElementById('edit_brand_status').checked = status == 1;
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('delete-modal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteBrandForm').action = "{{ url('brands') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('view-brand');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var logo = button.getAttribute('data-logo');
            var logoImg = document.getElementById('view_brand_logo');
            var logoLog = document.getElementById('view_brand_logo_log');
            if (logo) {
                logoImg.src = "{{ asset('storage') }}/" + logo;
                logoLog.style.display = "none";
            } else {
                logoImg.src = "{{ asset('admin/no-image.png') }}";
                logoLog.textContent = "No logo available for this brand.";
                logoLog.style.display = "block";
            }
            document.getElementById('view_brand_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_brand_slug').textContent = button.getAttribute('data-slug');
            document.getElementById('view_brand_description').textContent = button.getAttribute('data-description');
            document.getElementById('view_brand_status').textContent = button.getAttribute('data-status');
        });
    }

    // Status Filter
    document.querySelectorAll('.dropdown-item.rounded-1').forEach(function(item) {
        item.addEventListener('click', function() {
            var filter = this.textContent.trim();
            document.querySelectorAll('tbody tr').forEach(function(row) {
                var statusCell = row.querySelector('td span.badge');
                if (!statusCell) {
                    row.style.display = '';
                    return;
                }
                var isActive = statusCell.textContent.trim() === 'Active';
                if (filter === 'Active' && !isActive) {
                    row.style.display = 'none';
                } else if (filter === 'Inactive' && isActive) {
                    row.style.display = 'none';
                } else {
                    row.style.display = '';
                }
            });
        });
    });
});
</script>





