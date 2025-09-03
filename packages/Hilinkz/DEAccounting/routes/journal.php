<?php

use Hilinkz\DEAccounting\Http\Controllers\DeJournalController;

Route::get('/journals', [DeJournalController::class, 'index'])->name('de-journal.index');
