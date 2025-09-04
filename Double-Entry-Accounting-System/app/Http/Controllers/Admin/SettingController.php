<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the settings page
     */
    public function index()
    {
        // Get company ID from auth user's tenant_id
        $companyId = auth()->user()->tenant_id;
        
        if (!$companyId) {
            return redirect()->back()->with('error', 'Company ID not found!');
        }

        // Get current company from tenant database
        $company = Company::find($companyId);
        
        if (!$company) {
            return redirect()->back()->with('error', 'Company not found!');
        }

        // Get config from company
        $config = json_decode($company->config ?? '{}', true);
        
        // Get settings with defaults
        $settings = [
            'show_journal_in_sale_invoice' => $config['show_journal_in_sale_invoice'] ?? false,
            'show_journal_in_purchase_bill' => $config['show_journal_in_purchase_bill'] ?? false,
        ];
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'show_journal_in_sale_invoice' => 'nullable|in:on,1,true',
            'show_journal_in_purchase_bill' => 'nullable|in:on,1,true',
        ]);

        // Get company ID from auth user's tenant_id
        $companyId = auth()->user()->tenant_id;
        
        if (!$companyId) {
            return redirect()->back()->with('error', 'Company ID not found!');
        }

        // Convert checkbox values to proper boolean
        $config = [
            'show_journal_in_sale_invoice' => $request->has('show_journal_in_sale_invoice'),
            'show_journal_in_purchase_bill' => $request->has('show_journal_in_purchase_bill'),
        ];

        $company = Company::find($companyId);

        if (!$company) {
            return redirect()->back()->with('error', 'Company not found!');
        }

        // Update in tenant database (current connection)
        try {
            DB::beginTransaction();
            
            $tenantUpdated =  DB::table($company->db_name . '.companies')
                ->where('id', $companyId)
                ->update([
                    'config' => json_encode($config),
                    'updated_at' => now()
                ]);
            
            if (!$tenantUpdated) {
                throw new \Exception('Failed to update tenant database');
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update tenant database: ' . $e->getMessage());
        }

        // Update in landlord database (primary database)
        try {
            DB::connection('landlord')->beginTransaction();
            
            $landlordUpdated = DB::connection('landlord')
                ->table('companies')
                ->where('id', $companyId)
                ->update([
                    'config' => json_encode($config),
                    'updated_at' => now()
                ]);
            
            if (!$landlordUpdated) {
                throw new \Exception('Failed to update landlord database');
            }
            
            DB::connection('landlord')->commit();
            
        } catch (\Exception $e) {
            DB::connection('landlord')->rollBack();
            \Log::error('Failed to update landlord database: ' . $e->getMessage());
            // Don't fail the main operation, just log the error
        }

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
