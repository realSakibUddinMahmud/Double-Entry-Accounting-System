{{-- filepath: /opt/lampp/htdocs/ryogas/hilinkz-inventory/resources/views/admin/purchase/payment/journal-modals.blade.php --}}

<!-- View Journal Modal -->
<div class="modal fade" id="view-journal-{{ $journal->id }}" tabindex="-1" aria-labelledby="viewJournalLabel{{ $journal->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewJournalLabel{{ $journal->id }}">Journal Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Date:</dt>
                    <dd class="col-sm-7">{{ \Carbon\Carbon::parse($journal->payment_date)->format('d/m/Y') }}</dd>

                    <dt class="col-sm-5">Paid From:</dt>
                    <dd class="col-sm-7">
                        {{ $journal->creditTransaction->account->title ?? 'N/A' }}<br>
                        <small class="text-muted">
                            {{ $journal->creditTransaction->account->accountable_alias ?? '-' }} -
                            {{ $journal->creditTransaction->account->accountable->name ?? ($journal->creditTransaction->account->accountable->title ?? 'N/A') }}
                        </small>
                    </dd>

                    <dt class="col-sm-5">Note:</dt>
                    <dd class="col-sm-7">{{ $journal->note }}</dd>

                    <dt class="col-sm-5">Amount:</dt>
                    <dd class="col-sm-7">{{ number_format($journal->amount, 2) }}</dd>

                    @if($journal->files && $journal->files->count())
                        <dt class="col-sm-5">Attachments:</dt>
                        <dd class="col-sm-7">
                            <ul class="list-unstyled mb-0">
                                @foreach($journal->files as $file)
                                    <li>
                                        <a href="{{ $file->path }}" target="_blank" download>
                                            <i class="ti ti-download"></i> {{ $file->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </dd>
                    @endif
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Journal Modal -->
<div class="modal fade" id="delete-journal-{{ $journal->id }}" tabindex="-1" aria-labelledby="deleteJournalLabel{{ $journal->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-5">
            <form action="{{ route('purchases.payments.destroy', [$purchase->id, $journal->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center p-0">
                    <span class="rounded-circle d-inline-flex p-2 bg-danger-transparent mb-2">
                        <i class="ti ti-trash fs-24 text-danger"></i>
                    </span>
                    <h4 class="fs-20 text-gray-9 fw-bold mb-2 mt-1">Delete Payment</h4>
                    <p class="text-gray-6 mb-0 fs-16">Are you sure you want to delete this Payment entry?</p>
                    <div class="d-flex justify-content-center mt-3">
                        <button type="button" class="btn me-2 btn-secondary fs-13 fw-medium p-2 px-3 shadow-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger fs-13 fw-medium p-2 px-3">Yes Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>