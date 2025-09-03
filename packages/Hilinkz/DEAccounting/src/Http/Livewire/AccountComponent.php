<?php

namespace Hilinkz\DEAccounting\Http\Livewire;

use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountType;
use Hilinkz\DEAccounting\Models\DeBank;
use Hilinkz\DEAccounting\Models\DeBankAccount;
use Livewire\Component;
use Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class AccountComponent extends Component
{
    use UsesTenantConnection;
    public $account_no;
    public $title;
    public $account_type_id;
    public $accountable_type;
    public $accountable_id = [];
    public $parent_title;
    public $root_type;

    public $accountTypes = [];
    public $accounts = [];
    public $banks = [];
    public $accountables = [];
    public $rootTypes = [];
    public $accountable_data = [];
    public $selected_account_type;

    // Extra fields for Bank
    public $bank_id;
    public $branch;
    public $bank_ac_no;
    public $ac_holder_name;

    // Extra fields for edit
    public $isEdit = false;
    public $account_id;

    public function changeAccountType($value)
    {
        $accountType = DeAccountType::find($value);
        $this->selected_account_type = $accountType->title;
    }
    public function changeAccountableType($value)
    {
        $accountables = DeAccount::$accountables;

        $match = collect($accountables)->firstWhere('class_id', $value);

        if ($match && class_exists($match['class'])) {
            $className = $match['class'];
            $model = new $className;

            // Get columns of the model's table
            $columns = \Schema::getColumnListing($model->getTable());

            // Order by title if exists, otherwise name
            if (in_array('title', $columns)) {
                $this->accountable_data = $className::orderBy('title')->get();
            } elseif (in_array('name', $columns)) {
                $this->accountable_data = $className::orderBy('name')->get();
            } else {
                $this->accountable_data = $className::all();
            }

            $this->accounts = DeAccount::where('accountable_type', $value)
                ->groupBy('title')
                ->orderBy('title')
                ->pluck('title');
        } else {
            $this->accountable_data = [];
            $this->accounts = [];
        }
    }


    public function mount($accountId = null)
    {
        $this->isEdit = !is_null($accountId);

        if ($this->isEdit) {
            $account = DeAccount::findOrFail($accountId);
            $this->account_id = $accountId;
            $this->account_no = $account->account_no;
            $this->title = $account->title;
            $this->account_type_id = $account->account_type_id;
            $this->accountable_type = $account->accountable_type;
            $this->accountable_id = [$account->accountable_id];
            $this->root_type = $account->root_type;

            // Load parent account if exists
            if ($account->parent_id) {
                $parent = DeAccount::find($account->parent_id);
                $this->parent_title = $parent ? $parent->title : null;
            }

            // Load bank account if exists
            if ($account->bankAccount) {
                $this->bank_id = $account->bankAccount->bank_id;
                $this->bank_ac_no = $account->bankAccount->account_no;
                $this->ac_holder_name = $account->bankAccount->account_name;
                $this->branch = $account->bankAccount->branch;
            }

            // Trigger the change events
            $this->changeAccountType($this->account_type_id);
            $this->changeAccountableType($this->accountable_type);
        }
    }

    public function render()
    {
        $this->accountTypes = DeAccountType::all();
        $this->banks = DeBank::orderBy('bank_name')->get();
        $this->accountables = DeAccount::$accountables;
        $this->rootTypes = DeAccount::$rootTypes;

        if ($this->isEdit) {
            return view('de-accounting::livewire.account.update');
        }

        return view('de-accounting::livewire.account.create');
    }

    public function store()
    {
        $connection = $this->getConnectionName();

        $validator = Validator::make([
            'account_no'        => $this->account_no,
            'title'             => $this->title,
            'account_type_id'   => $this->account_type_id,
            'accountable_type'  => $this->accountable_type,
            'accountable_id'    => $this->accountable_id,
            'root_type'         => $this->root_type,
        ], [
            'account_no' => [
                'nullable',
                'integer',
                Rule::unique($connection . '.accounts', 'account_no')
            ],
            'title' => [
                'required',
                Rule::unique($connection . '.accounts', 'title')
                    ->where(function ($query) {
                        return $query
                            ->where('accountable_type', $this->accountable_type)
                            ->whereIn('accountable_id', $this->accountable_id);
                    })
            ],
            'account_type_id' => 'required',
            'accountable_type' => 'required',
            'accountable_id' => 'required|array|min:1',
            'root_type' => 'required',
        ]);

        $validator->validate();

        DeAccount::fixTree();

        foreach ($this->accountable_id as $key => $value) {
            if ($this->accountable_id[$key] != NULL) {

                if (isset($this->parent_title)) {
                    $parent_ac = DeAccount::where('title', $this->parent_title)
                        ->where('accountable_type', $this->accountable_type)
                        ->where('accountable_id', $this->accountable_id[$key])
                        ->first();

                    if (isset($parent_ac)) {
                        $parent_ac_id = $parent_ac->id;
                    } else {
                        $parent_ac_id = NULL;
                        continue; // Skip this iteration if parent account is not found
                    }
                }

                $account[$key] = new DeAccount();
                $account[$key]->account_no = $this->account_no;
                $account[$key]->title = $this->title;
                $account[$key]->account_type_id = $this->account_type_id;
                $account[$key]->accountable_type = $this->accountable_type;
                $account[$key]->accountable_id = $this->accountable_id[$key];
                $account[$key]->created_by = Auth::user()->id ?? null;
                $account[$key]->status = 'ACTIVE';
                $account[$key]->parent_id = $parent_ac_id ?? null;
                $account[$key]->root_type = $this->root_type;

                $account[$key]->save();

                $account_type[$key] = DeAccountType::find($this->account_type_id);
                if ($account_type[$key] && $account_type[$key]->title == 'Bank') {
                    $bankAccount[$key] = new DeBankAccount();
                    $bankAccount[$key]->account_id = $account[$key]->id;
                    $bankAccount[$key]->bank_id = $this->bank_id;
                    $bankAccount[$key]->account_no = $this->bank_ac_no;
                    $bankAccount[$key]->account_name = $this->ac_holder_name;
                    $bankAccount[$key]->branch = $this->branch;
                    $bankAccount[$key]->status = 'ACTIVE';

                    $bankAccount[$key]->save();
                }
            }
        }
        $this->reset();
        session()->flash('message', 'New account created successfully.');
        // return redirect()->route('de-account.create');
    }

    public function update()
    {
        $connection = $this->getConnectionName();

        $validator = Validator::make([
            'account_no'        => $this->account_no,
            'title'             => $this->title,
            'account_type_id'   => $this->account_type_id,
            'accountable_type'  => $this->accountable_type,
            'accountable_id'    => $this->accountable_id,
            'root_type'         => $this->root_type,
        ], [
            'account_no' => [
                'nullable',
                'integer',
                Rule::unique($connection . '.accounts', 'account_no')
                    ->ignore($this->account_id)
            ],
            'title' => [
                'required',
                Rule::unique($connection . '.accounts', 'title')
                    ->where(function ($query) {
                        return $query
                            ->where('accountable_type', $this->accountable_type)
                            ->whereIn('accountable_id', $this->accountable_id);
                    })
                    ->ignore($this->account_id)
            ],
            'account_type_id' => 'required',
            'accountable_type' => 'required',
            'accountable_id' => 'required|array|min:1',
            'root_type' => 'required',
        ]);

        $validator->validate();

        foreach ($this->accountable_id as $key => $value) {
            if ($this->accountable_id[$key] != NULL) {

                if (isset($this->parent_title)) {
                    $parent_ac = DeAccount::where('title', $this->parent_title)
                        ->where('accountable_type', $this->accountable_type)
                        ->where('accountable_id', $this->accountable_id[$key])
                        ->first();

                    if (isset($parent_ac)) {
                        $parent_ac_id = $parent_ac->id;
                    } else {
                        $parent_ac_id = NULL;
                        continue;
                    }
                }

                // Maintain the array syntax for consistency
                $account[$key] = DeAccount::findOrFail($this->account_id);

                $account[$key]->account_no = $this->account_no;
                $account[$key]->title = $this->title;
                $account[$key]->account_type_id = $this->account_type_id;
                $account[$key]->accountable_type = $this->accountable_type;
                $account[$key]->accountable_id = $this->accountable_id[$key];
                $account[$key]->created_by = Auth::user()->id ?? null;
                $account[$key]->status = 'ACTIVE';
                $account[$key]->parent_id = $parent_ac_id ?? null;
                $account[$key]->root_type = $this->root_type;

                $account[$key]->save();

                $account_type[$key] = DeAccountType::find($this->account_type_id);
                if ($account_type[$key] && $account_type[$key]->title == 'Bank') {
                    if ($account[$key]->bankAccount) {
                        $bankAccount[$key] = $account[$key]->bankAccount;
                    } else {
                        $bankAccount[$key] = new DeBankAccount();
                        $bankAccount[$key]->account_id = $account[$key]->id;
                    }

                    $bankAccount[$key]->bank_id = $this->bank_id;
                    $bankAccount[$key]->account_no = $this->bank_ac_no;
                    $bankAccount[$key]->account_name = $this->ac_holder_name;
                    $bankAccount[$key]->branch = $this->branch;
                    $bankAccount[$key]->status = 'ACTIVE';
                    $bankAccount[$key]->save();
                } elseif ($account[$key]->bankAccount) {
                    $account[$key]->bankAccount->delete();
                }
            }
        }

        session()->flash('message', 'Existing account updated successfully.');
        return redirect()->route('de-account.index');
    }
}
