@extends('layouts.app-admin')
@section('title', 'Category')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Categories</h4>
            <h6>Manage your Categories</h6>
        </div>
    </div>
    @can('category-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category">
            <i class="ti ti-circle-plus me-1"></i>Add Category
        </a>
    </div>
    @endcan
</div>
<!-- /category list -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Search & Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('categories.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search Category Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search by category name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select">
                    <option value="">All Categories</option>
                    <option value="none" {{ request('parent_id') === 'none' ? 'selected' : '' }}>No Parent (Root)</option>
                    @foreach($parentCategories as $parentCategory)
                        <option value="{{ $parentCategory->id }}" {{ request('parent_id') == $parentCategory->id ? 'selected' : '' }}>
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1"></i>Search
                </button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="ti ti-refresh me-1"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <h5 class="card-title mb-0">Category List</h5>
        <div class="ms-auto">
            <span class="text-muted">Total: {{ $categories->total() }} categories</span>
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
                        <th>Category</th>
                        <th>Parent</th>
                        <th>Created At</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $category->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td class="text-gray-9">
                                {{ $category->name }}
                            </td>
                            <td>
                                {{ optional($category->parent)->name ?? '-' }}
                            </td>
                            <td>
                                {{ $category->created_at ? $category->created_at->format('d-m-Y') : '-' }}
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('category-view')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-category"
                                       data-id="{{ $category->id }}"
                                       data-name="{{ $category->name }}"
                                       data-parent_id="{{ $category->parent_id }}"
                                       data-parent="{{ optional($category->parent)->name }}"
                                       data-created_at="{{ $category->created_at }}"
                                       data-path="{{ $category->ancestors->pluck('name')->implode(' > ') }}{{ $category->ancestors->count() ? ' > ' : '' }}{{ $category->name }}">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    @can('category-edit')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit-category"
                                       data-id="{{ $category->id }}"
                                       data-name="{{ $category->name }}"
                                       data-parent_id="{{ $category->parent_id }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    @can('category-delete')
                                    <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-modal"
                                       data-id="{{ $category->id }}">
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
        <x-admin.pagination :paginator="$categories" info-text="categories" />
    </div>
</div>
<!-- /category list -->

@can('category-create')
@include('admin.category.create-modal')
@endcan
@can('category-edit')
@include('admin.category.edit-modal')
@endcan
@can('category-delete')
@include('admin.category.delete-modal')
@endcan
@can('category-view')
@include('admin.category.view-modal')
@endcan
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal
    var editModal = document.getElementById('edit-category');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var parent_id = button.getAttribute('data-parent_id');

            document.getElementById('editCategoryForm').action = "{{ url('categories') }}/" + id;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('edit_category_parent_id').value = parent_id;
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('delete-modal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            document.getElementById('deleteCategoryForm').action = "{{ url('categories') }}/" + id;
        });
    }

    // View Modal
    var viewModal = document.getElementById('view-category');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('view_category_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_category_parent').textContent = button.getAttribute('data-parent');
            document.getElementById('view_category_created_at').textContent = button.getAttribute('data-created_at');
        });
    }
});
</script>





