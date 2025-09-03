@foreach($journals as $journal)
    @if($journal->files && $journal->files->count() > 0)
    <div class="modal fade" id="filesModal{{ $journal->id }}" tabindex="-1" aria-labelledby="filesModalLabel{{ $journal->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Attached Files</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        @foreach($journal->files as $file)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    {{ $file->title }}
                                </div>
                                <div class="btn-group">
                                    <a href="{{ asset($file->path) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteFileModal{{ $file->id }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @include('de-accounting::expenses.delete-file-modal')
    @endif
@endforeach