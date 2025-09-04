<?php

use Hilinkz\DEAccounting\Http\Controllers\DeLedgerController;

Route::get('/ledgers', [DeLedgerController::class, 'index'])->name('de-ledger.index');
