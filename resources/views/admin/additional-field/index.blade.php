@extends('layouts.app-admin')
@section('title', 'Additional Fields')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Additional Fields</h4>
            <h6>Manage your Additional Fields</h6>
        </div>
    </div>
    @can('additional-field-create')
    <div class="page-btn">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-field">
            <i class="ti ti-circle-plus me-1"></i>Add Field
        </a>
    </div>
    @endcan
</div>
<!-- /additional field list -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
        <div class="search-set">
            <input type="text" id="fieldTableSearch" class="form-control form-control-sm" placeholder="Search fields...">
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
                        <th>Field For</th>
                        <th>Name</th>
                        <th>Label</th>
                        <th>Type</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fields as $field)
                        <tr>
                            <td>
                                <label class="checkboxs">
                                    <input type="checkbox" value="{{ $field->id }}">
                                    <span class="checkmarks"></span>
                                </label>
                            </td>
                            <td>
                                {{ class_basename($field->model_type) }}
                            </td>
                            <td class="text-gray-9">{{ $field->name }}</td>
                            <td>{{ $field->label }}</td>
                            <td>{{ $field->type }}</td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    @can('additional-field-view')
                                    <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#view-field"
                                       data-id="{{ $field->id }}"
                                       data-model_type="{{ strtolower(class_basename($field->model_type)) }}"
                                       data-name="{{ $field->name }}"
                                       data-label="{{ $field->label }}"
                                       data-type="{{ $field->type }}"
                                       data-options="{{ $field->type === 'select' ? $field->options : '' }}">
                                        <i data-feather="eye" class="feather-eye"></i>
                                    </a>
                                    @endcan
                                    @can('additional-field-edit')
                                        <a class="me-2 p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#edit-field"
                                       data-id="{{ $field->id }}"
                                       data-model_type="{{ strtolower(class_basename($field->model_type)) }}"
                                       data-name="{{ $field->name }}"
                                       data-label="{{ $field->label }}"
                                       data-type="{{ $field->type }}"
                                       data-options="{{ $field->type === 'select' ? $field->options : '' }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    @can('additional-field-delete')
                                        <a class="p-2"
                                       href="javascript:void(0);"
                                       data-bs-toggle="modal"
                                       data-bs-target="#delete-field-modal"
                                       data-id="{{ $field->id }}">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                    @endcan
                                    
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- No additional fields --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- /additional field list -->

@include('admin.additional-field.create-modal')
@include('admin.additional-field.edit-modal')
@include('admin.additional-field.delete-modal')
@include('admin.additional-field.view-modal')
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Table search
    const searchInput = document.getElementById('fieldTableSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Edit Modal
    var editModal = document.getElementById('edit-field');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('editFieldForm').action = "{{ url('additional-fields') }}/" + button.getAttribute('data-id');
            document.getElementById('edit_field_model_type').value = button.getAttribute('data-model_type');
            document.getElementById('edit_field_name').value = button.getAttribute('data-name');
            document.getElementById('edit_field_label').value = button.getAttribute('data-label');
            document.getElementById('edit_field_type').value = button.getAttribute('data-type');
            // If you want to handle options in edit modal, add similar logic here
        });
    }

    // Delete Modal
    var deleteModal = document.getElementById('delete-field-modal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('deleteFieldForm').action = "{{ url('additional-fields') }}/" + button.getAttribute('data-id');
        });
    }

    // View Modal
    var viewModal = document.getElementById('view-field');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('view_field_model_type').textContent = button.getAttribute('data-model_type');
            document.getElementById('view_field_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_field_label').textContent = button.getAttribute('data-label');
            document.getElementById('view_field_type').textContent = button.getAttribute('data-type');
            // Handle select options (comma separated)
            var optionsLabel = document.getElementById('view_field_options_label');
            var optionsValue = document.getElementById('view_field_options');
            var type = button.getAttribute('data-type');
            var options = button.getAttribute('data-options');
            if(type === 'select' && options) {
                optionsLabel.style.display = '';
                optionsValue.style.display = '';
                optionsValue.textContent = options;
            } else {
                optionsLabel.style.display = 'none';
                optionsValue.style.display = 'none';
                optionsValue.textContent = '';
            }
        });
    }
});
</script>





