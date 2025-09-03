<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Store; // Add this at the top if not already present

use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $stores = Store::all();
        return view('admin.store.index', compact('stores'));
    }
    /**
     * Store a new item in the store.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'status' => 'boolean',
            'contact_no' => 'required|string|max:255',
        ]);

        // Store the data in the database
        Store::create([
            'name' => $request->name,
            'address' => $request->address,
            'status' => $request->has('status'), // true if checked, false if not
            'contact_no' => $request->contact_no,
        ]);

        // Redirect back with a success message
        return back()->with('success', 'Store added successfully!');
    }
    /**
     * Update an existing item in the store.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Store $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'status' => 'boolean',
            'contact_no' => 'required|string|max:255',
        ]);

        $store->update([
            'name' => $request->name,
            'address' => $request->address,
            'status' => $request->has('status'), // true if checked, false if not
            'contact_no' => $request->contact_no,
        ]);

        return back()->with('success', 'Store updated successfully!');
    }

    /**
     * Remove the specified store from the database.
     *
     * @param \App\Models\Store $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Store $store)
    {
        // Check if the store has any products
        $hasProducts = $store->products()->exists();

        // Check if the store has any accounts
        $hasAccounts = $store->accounts()->exists();

        // Check if any of the store's accounts have transactions (assuming transactions relation)
        $hasAccountTransactions = false;
        if ($hasAccounts) {
            foreach ($store->accounts()->get() as $account) {
                if ($account->transactions()->exists()) {
                    $hasAccountTransactions = true;
                    break;
                }
            }
        }

        if ($hasProducts || $hasAccountTransactions) {
            // Don't delete, just archive
            $store->update(['status' => false]);
            return back()->with('warning', 'Store has products or account transactions. Status set to Archived instead of deleted.');
        }

        // If no products and no account transactions, delete the store
        $store->delete();
        return back()->with('success', 'Store deleted successfully!');
    }
}
