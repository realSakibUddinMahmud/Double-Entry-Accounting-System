<?php

namespace Hilinkz\DEAccounting\Http\Livewire;

use Livewire\Component;
use App\Models\GasStation;

class DeJournalSearchComponent extends Component
{
    public $gs_id = null;

    protected $queryString = [
        'gs_id' => ['except' => ''],
    ];

    public function mount()
    {
        $this->gs_id = request('gs_id') ?? null;
    }

    public function render()
    {
        return view('de-accounting::livewire.component.de-journal-search');
    }
}