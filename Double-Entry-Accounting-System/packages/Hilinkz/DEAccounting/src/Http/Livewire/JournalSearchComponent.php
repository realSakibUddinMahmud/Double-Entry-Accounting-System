<?php

namespace Hilinkz\DEAccounting\Http\Livewire;

use Livewire\Component;
use Hilinkz\DEAccounting\Models\DeAccount;

class JournalSearchComponent extends Component
{
    public $accountables = [];
    public $accountable_data = [];

    public $accountable_type = null;
    public $accountable_id = null;
    public $selected_accountable_type_alias = null;
    public $start_date = null;

    protected $queryString = [
        'accountable_type' => ['except' => ''],
        'accountable_id' => ['except' => ''],
    ];

    public function mount()
    {
        $this->accountables = DeAccount::$accountables;

        if ($this->accountable_type) {
            $this->loadAccountableData($this->accountable_type);
        }
    }

    public function changeAccountableType($value)
    {
        $this->accountable_id = null;
        $this->loadAccountableData($value);
    }

    public function loadAccountableData($classId)
    {
        $match = collect($this->accountables)->firstWhere('class_id', $classId);

        if ($match) {
            $this->selected_accountable_type_alias = $match['alias'] ?? null;

            if (class_exists($match['class'])) {
                $modelClass = $match['class'];

                // Check which column exists and order accordingly
                $columns = \Schema::getColumnListing((new $modelClass)->getTable());

                if (in_array('title', $columns)) {
                    $this->accountable_data = $modelClass::orderBy('title')->get();
                } elseif (in_array('name', $columns)) {
                    $this->accountable_data = $modelClass::orderBy('name')->get();
                }else {
                    $this->accountable_data = $modelClass::all();
                }

                return;
            }
        }

        $this->selected_accountable_type_alias = null;
        $this->accountable_data = [];
    }

    public function render()
    {
        return view('de-accounting::livewire.component.journal-search');
    }
}
