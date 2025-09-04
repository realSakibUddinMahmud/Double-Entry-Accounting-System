<?php

namespace Hilinkz\DEAccounting\Http\Livewire;

use Livewire\Component;
use App\Models\GasStation;
use Hilinkz\DEAccounting\Models\DeAccount;

// class DeLedgerSearchComponent extends Component
// {
//     public $gs_id = null;
//     public $account_title = null;
//     public $accounts = [];

//     protected $queryString = [
//         'gs_id' => ['except' => ''],
//         'account_title' => ['except' => ''],
//     ];

//     public function mount()
//     {
//         $this->gs_id = request('gs_id') ?? null;
//         $this->account_title = request('account_title') ?? null;
//         $this->accounts = DeAccount::select('title')
//             ->where('status', 'ACTIVE')
//             ->where('parent_id', '!=', null)
//             ->groupBy('title')
//             ->get();
//     }

//     public function render()
//     {
//         return view('de-accounting::livewire.component.de-ledger-search');
//     }
// }

class DeLedgerSearchComponent extends Component
{
    public $accountables = [];
    public $accountable_data = [];
    public $accounts = []; // Add this for account titles
    
    public $accountable_type = null; // This will hold the class ID of the accountable type
    public $accountable_id = null;  // This will hold the ID of the selected accountable entity
    public $account_title = null; // Add this for final account selection
    public $selected_accountable_type_alias = null; 

    protected $queryString = [
        'accountable_type' => ['except' => ''],
        'accountable_id' => ['except' => ''],
        'account_title' => ['except' => ''], // Add this
    ];

    public function mount()
    {
        $this->accountables = DeAccount::$accountables;
        $this->account_title = request('account_title') ?? null;
        $this->accountable_type = request('accountable_type') ?? null;
        $this->accountable_id = request('accountable_id') ?? null;

        if ($this->accountable_type) {
            $this->loadAccountableData($this->accountable_type);  // Load accountable id based on the type
        }
        
        if ($this->accountable_id) {
            $this->loadAccounts(); // Load accounts when accountable_id is set
        }
    }

    public function changeAccountableType($value)
    {
        $this->accountable_type = $value;
        $this->accountable_id = null;
        $this->account_title = null;
        $this->accountable_data = [];
        $this->accounts = [];
        
        if ($value) {
            $this->loadAccountableData($value);
        }
    }

    public function updatedAccountableId($value)
    {
        $this->accountable_id = $value;
        $this->account_title = null;
        $this->accounts = [];
        
        if ($value) {
            $this->loadAccounts();
        }
    }

    public function loadAccountableData($classId)
    {
        $match = collect($this->accountables)->firstWhere('class_id', $classId);

        if ($match) {
            $this->selected_accountable_type_alias = $match['alias'] ?? null;

            if (class_exists($match['class'])) {
                $modelClass = $match['class'];
                $columns = \Schema::getColumnListing((new $modelClass)->getTable());

                if (in_array('title', $columns)) {
                    $this->accountable_data = $modelClass::orderBy('title')->get();
                } elseif (in_array('name', $columns)) {
                    $this->accountable_data = $modelClass::orderBy('name')->get();
                } else {
                    $this->accountable_data = $modelClass::all();
                }
                return;
            }
        }

        $this->selected_accountable_type_alias = null;
        $this->accountable_data = [];
    }

    public function loadAccounts()
    {
        // $this->accounts = DeAccount::select('title')
        //     ->where('status', 'ACTIVE')
        //     ->where('parent_id', '!=', null)
        //     ->groupBy('title')
        //     ->get();

        $this->accounts = DeAccount::where('accountable_type', $this->accountable_type)
            ->where('accountable_id', $this->accountable_id)
            ->where('status', 'ACTIVE')
            ->where('parent_id', '!=', null)
            ->distinct() // Ensure distinct titles
            ->get();
    }

    public function render()
    {
        return view('de-accounting::livewire.component.de-ledger-search');
    }
}
