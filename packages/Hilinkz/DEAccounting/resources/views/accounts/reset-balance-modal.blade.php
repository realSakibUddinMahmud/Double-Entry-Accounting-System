<div class="modal fade" id="reset_balance_warning_modal" tabindex="-1" role="dialog"
    aria-labelledby="reset_balance_warning_modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="reset_balance_warning_form">
                    @method('POST')
                    @csrf
                    <input type="hidden" name="account_id" id="account_id" value="">
                    <div class="text-center my-3">
                        <img src="{{ asset('/img/wallet.png') }}" alt="" style="height: 100px; width: 100px">
                    </div>
                    <div class="text-center display-5 font-weight-bold">
                        Want to reset the <span class="text-danger">Closing Balance</span> ?
                    </div>
                    <div>
                        <label for="date">Date</label>
                        <input class="form-control" type="date" name="date" required>
                    </div>
                    <div>
                        <label for="date">Balance amount</label>
                        <input class="form-control" type="text" name="balance" placeholder="Enter balance amount"
                            pattern="^-?\d*(\.\d{0,2})?$" required>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="submit" class="btn btn-theme-orange">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
