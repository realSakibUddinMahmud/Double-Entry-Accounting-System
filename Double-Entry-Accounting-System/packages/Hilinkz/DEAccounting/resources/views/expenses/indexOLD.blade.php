@extends('admin.layouts.new_admin')

@section('impersonate_leave')
    @include('admin.layouts.impersonate-leave')
@endsection

@section('custom_style')
    @include('styles.data-table')
    @include('styles.general')
@endsection

@section('title', 'Expenses | RyoGas')

@section('content')
    <!-- Content Header -->
    <section class="content" id="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card m-0 p-0">
                        <div class="card-body py-0">
                            <div class="row">
                                <div class="col-md-6 offset-md-3 d-flex justify-content-center align-items-center">
                                    <p class="page-name m-0 text-center">Expenses</p>
                                </div>
                                <div class="col-md-3 d-flex justify-content-end align-items-center">
                                    <a href="{{ route('de-expense.create') }}">
                                        <button class="btn btn-primary datatable-btn">
                                            <i class="fas fa-plus text-white mr-2"></i> New Expense
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content Header Ends -->

    <!-- Main Content -->
    <section class="content my-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <div class="card card-body mb-3">
                        @livewire('de-accounting::journal-search-component')
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example1" class="table table-striped">
                                    <thead class="text-white font-weight-bold">
                                        <tr>
                                            <th>SL</th>
                                            <th class="text-center">Date</th>
                                            <th class="text-left">Expense of</th>
                                            <th class="text-left">Source Account</th>
                                            <th class="text-left">Purpose</th>
                                            <th class="text-left">Note</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($journals as $index => $journal)
                                            <tr>
                                                <td>{{ $journals->firstItem() + $index }}</td>
                                                <td class="text-center">{{ date('d/m/Y', strtotime($journal->date)) }}</td>
                                                <td class="text-left">
                                                    {{ class_basename($journal->journalable_alias) }} -
                                                    {{ $journal->journalable->name ?? ($journal->journalable->title ?? 'N/A') }}
                                                </td>
                                                <td class="text-left">
                                                    Title: {{ $journal->creditTransaction->account->title ?? 'N/A' }}<br>
                                                    @if (!empty($journal->creditTransaction->account->account_no))
                                                        No: {{ $journal->creditTransaction->account->account_no }}<br>
                                                    @endif
                                                </td>
                                                <td class="text-left">
                                                    Title: {{ $journal->debitTransaction->account->title ?? 'N/A' }}<br>
                                                    @if (!empty($journal->debitTransaction->account->account_no))
                                                        No: {{ $journal->debitTransaction->account->account_no }}<br>
                                                    @endif
                                                </td>
                                                <td class="text-left">{{ $journal->note??null }}</td>
                                                <td class="text-right">{{ $journal->amount }}</td>
                                                <td class="text-right">
                                                    @if ($journal->files && $journal->files->count() > 0)
                                                        <button type="button" class="btn btn-sm btn-fa-paperclip"
                                                            data-toggle="modal" data-target="#filesModal{{ $journal->id }}">
                                                            <i class="fas fa-paperclip text-success" aria-hidden="true"></i>
                                                        </button>
                                                    @endif
                                                    @can('expense-delete')
                                                        <button type="button" class="btn btn-sm btn-far-fa-trash-alt"
                                                            data-toggle="modal" data-target="#confirmDeleteModal"
                                                            data-id="{{ $journal->id }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        Showing {{ $journals->firstItem() }} to {{ $journals->lastItem() }} of {{ $journals->total() }} records
                                    </div>
                                    <div>
                                        {{ $journals->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- Main Content Ends -->

    @include('de-accounting::expenses.delete-modal')
    @include('de-accounting::expenses.files-modal')
@endsection

@push('scripts')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('admin_lte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('admin_lte/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

    <script>
        $(function() {
            $("#example1").DataTable({
                responsive: true,
                lengthChange: false,
                autoWidth: true,
                buttons: ["csv", "excel", "pdf", "print", "colvis"],
                paging: false,
                searching: true,
                ordering: true,
                info: false,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                pageLength: 25,
                columnDefs: [
                    {
                        targets: [6],
                        visible: true,
                        searchable: true
                    }
                ]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
@endpush
