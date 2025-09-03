<?php

namespace Hilinkz\DEAccounting\Http\Livewire;

use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeFile;
use Hilinkz\DEAccounting\Models\DE;
use Livewire\Component;
use Auth;
use Illuminate\Validation\Rule;
use Request;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class SecurityDepositComponent extends Component
{
    use WithFileUploads;
    use UsesTenantConnection;

    public $sourceAccountRootType = 3; //Liabilities
    public $destinationAccountRootType = 1; //Assets
    public $eventName = 'SECURITY-DEPOSIT';
    public $sourceAccountComponents = [];
    public $sourceAccounts, $destinationAccounts;
    public $disabled = true;
    public $accountables = [];
    public $source_accountable_data = [];
    public $destination_accountable_data = [];
    public $source_accountable_type = [];
    public $destination_accountable_type = [];
    public $attachments = [];

    public $source_accountable_id, $destination_accountable_id, $source_account_id, $source_amount, $destination_account_id, $destination_amount = 0, $note = null, $date;

    protected $rules = [
        'source_accountable_type' => 'required',
        'source_accountable_id' => 'required',
        'date' => 'required|date',
        'source_account_id' => 'required',
        'source_amount' => 'required|numeric|min:0.01',
        'destination_account_id' => 'required',
        'destination_amount' => 'required|numeric|min:0.01',
        'note'=> 'nullable',
        'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max per file
    ];

    public function updated($fields)
    {
       $this->disabled = true; 
       $this->validate();
       $this->disabled = false;
    }

    public function changeAccountType($value)
    {
        $accountType = DeAccountType::find($value);
        $this->selected_account_type = $accountType->title;
    }
    public function changeSourceAccountableType($value)
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
                $this->source_accountable_data = $className::orderBy('title')->get();
            } elseif (in_array('name', $columns)) {
                $this->source_accountable_data = $className::orderBy('name')->get();
            } else {
                $this->source_accountable_data = $className::all();
            }
        } else {
            $this->source_accountable_data = [];
        }
        $this->destination_accountable_data = $this->source_accountable_data;

    }

    public function changeSourceAccountableId($value)
    {
        $this->destination_accountable_type = $this->source_accountable_type;
        $this->destination_accountable_id = $this->source_accountable_id;
        $this->sourceAccounts = DeAccount::where('accountable_type', $this->source_accountable_type)->where('accountable_id', $value)->where('root_type',$this->sourceAccountRootType)->where('parent_id','!=',NULL)->orderBy('title')->get();
        $this->destinationAccounts = DeAccount::where('accountable_type', $this->destination_accountable_type)->where('accountable_id', $value)->where('root_type',$this->destinationAccountRootType)->where('parent_id','!=',NULL)->orderBy('title')->get();
    }

    public function changeAmount($value)
    {
        $this->destination_amount = $this->source_amount = $value;
    }


    public function render()
    {
        $this->accountables = DeAccount::$accountables;
        return view('de-accounting::livewire.security-deposit.create');
    }

    public function store()
    {
        $this->validate();

        $requestedData = array();
        $requestedData['date'] = $this->date;
        $requestedData['source_accountable_id'] = $this->source_accountable_id;
        $requestedData['destination_accountable_id'] = $this->destination_accountable_id;
        $requestedData['source_accountable_type'] = $this->source_accountable_type;
        $requestedData['destination_accountable_type'] = $this->destination_accountable_type;
        $requestedData['note'] = $this->note;
        $requestedData['amount'] = $this->destination_amount;

        $sourceAccount = DeAccount::find($this->source_account_id);
        $destinationAccount = DeAccount::find($this->destination_account_id);

        $deType = 'UPUP';
        $taskId=null;
        $eventName = $this->eventName;
        $result = DE::store($sourceAccount,$destinationAccount,$requestedData,$deType,$taskId,$eventName);

        $readableEventName = ucwords(strtolower(str_replace('-', ' ', $this->eventName)));

        if ($result['status'] && $result['status'] == true) {

            if (!empty($this->attachments)) {
                DeFile::upload($this->attachments,$result);
            }

            $this->reset();
            session()->flash('message', ''.$readableEventName.' has been recorded successfully.');
        } else {
            session()->flash('error', 'Something went wrong. Please try again.');
        } 
        
    }
}
