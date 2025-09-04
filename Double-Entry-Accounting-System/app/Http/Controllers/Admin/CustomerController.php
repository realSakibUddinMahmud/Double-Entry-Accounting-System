<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Show all customers
    public function index()
    {
        $customers = Customer::latest()->get();
        return view('admin.customers.index', compact('customers'));
    }

    // // Show create form
    // public function create()
    // {
    //     return view('admin.customers.create');
    // }

    // Store new customer
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Customer::create($request->all());

        // return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
        return back()->with('success', 'Customer added successfully!');
    }

    // // Show single customer
    // public function show($id)
    // {
    //     $customer = Customer::findOrFail($id);
    //     return view('admin.customers.show', compact('customer'));
    // }

    // // Show edit form
    // public function edit($id)
    // {
    //     $customer = Customer::findOrFail($id);
    //     return view('admin.customers.edit', compact('customer'));
    // }

    // Update customer
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'boolean', // Ensure status is boolean
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $customer->update($request->all());

        // return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
        return back()->with('success', 'Customer updated successfully!');
    }

    // Delete customer
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        // Check if the customer has any sales
        $hasSales = $customer->sales()->exists();

        // Check if the customer has any accounts
        $hasAccounts = $customer->accounts()->exists();

        // Check if any of the customer's accounts have transactions
        $hasAccountTransactions = false;
        if ($hasAccounts) {
            foreach ($customer->accounts()->get() as $account) {
                if ($account->transactions()->exists()) {
                    $hasAccountTransactions = true;
                    break;
                }
            }
        }

        if ($hasSales || $hasAccountTransactions) {
            // Don't delete, just archive
            $customer->update(['status' => false]);
            return back()->with('warning', 'Customer has sales or account transactions. Status set to Archived instead of deleted.');
        }

        // If no sales and no account transactions, delete the customer
        $customer->accounts()->delete(); // Delete associated accounts if any
        $customer->delete();
        return back()->with('success', 'Customer deleted successfully!');
    }
}