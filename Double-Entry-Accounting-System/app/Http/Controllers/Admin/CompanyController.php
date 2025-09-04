<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\SessionMessageHelper;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        // $companies = Company::all();
        // $totalCompanies = Company::count();
        // $activeCompanies = Company::where('status', 'ACTIVE')->count();
        // $inactiveCompanies = Company::where('status', 'INACTIVE')->count();
        // $totalRegions = Company::distinct('region')->count('region');

        $companies = DB::connection('landlord')->table('companies')->get();
        $totalCompanies = DB::connection('landlord')->table('companies')->count();
        $activeCompanies = DB::connection('landlord')->table('companies')->where('status', 'ACTIVE')->count();
        $inactiveCompanies = DB::connection('landlord')->table('companies')->where('status', 'INACTIVE')->count();
        $totalRegions = DB::connection('landlord')->table('companies')->distinct('region')->count('region');

        // Get all tenants grouped by company_id
        $tenants = DB::connection('landlord')->table('tenants')
            ->select('company_id', 'domain')
            ->get()
            ->groupBy('company_id');

        // Add domains to each company
        $companies->each(function ($company) use ($tenants) {
            $company->domains = $tenants->get($company->id, collect())->pluck('domain');
        });

        // Create 3 dummy companies
        // $companies = collect([
        //     (object)[
        //     'id' => 1,
        //     'name' => 'Acme Corp',
        //     'region' => 'North',
        //     'office_address' => '123 Main St',
        //     'contact_no' => '1234567890',
        //     'email' => 'info@acme.com',
        //     'db_name' => 'acme_db',
        //     'status' => 'ACTIVE',
        //     'config' => null,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        //     ],
        //     (object)[
        //     'id' => 2,
        //     'name' => 'Beta Industries',
        //     'region' => 'South',
        //     'office_address' => '456 Market Ave',
        //     'contact_no' => '0987654321',
        //     'email' => 'contact@beta.com',
        //     'db_name' => 'beta_db',
        //     'status' => 'INACTIVE',
        //     'config' => null,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        //     ],
        //     (object)[
        //     'id' => 3,
        //     'name' => 'Gamma LLC',
        //     'region' => 'East',
        //     'office_address' => '789 Broadway',
        //     'contact_no' => '5551234567',
        //     'email' => 'hello@gamma.com',
        //     'db_name' => 'gamma_db',
        //     'status' => 'ACTIVE',
        //     'config' => null,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        //     ],
        // ]);

        // $totalCompanies = 100;
        // $activeCompanies = 80;
        // $inactiveCompanies = 20;
        // $totalRegions = 5;

        return view('admin.company.index', compact('companies', 'totalCompanies', 'activeCompanies', 'inactiveCompanies', 'totalRegions'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.company.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $primaryDB = env('DB_DATABASE') ?? null;
        $domain_name = request('domain_name') ?? null;

        $company = Company::where('contact_no', request('contact_no'))->first();
        if ($company) {
            return back()->with('error', 'Sorry! A company already registered with this phone number.');
        }

        $db_name = request('db_name');

        $cm_pid = DB::table($primaryDB . '.companies')->insertGetId(
            [
                'name' => request('name'),
                'region' => request('region'),
                'office_address' => request('office_address'),
                'contact_no' => request('contact_no'),
                'email' => request('email'),
                'db_name' => $db_name,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table($primaryDB . '.tenants')->insert(
            [
                'company_id' => $cm_pid,
                'name' => request('name'),
                'domain' => $domain_name,
                'database' => $db_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Company::createDB($db_name);

        $new_cm_pid = DB::table($db_name . '.companies')->insertGetId(
            [
                'id' => $cm_pid,
                'name' => request('name'),
                'region' => request('region'),
                'office_address' => request('office_address'),
                'contact_no' => request('contact_no'),
                'email' => request('email'),
                'db_name' => $db_name,
                'status' => 'ACTIVE',
                'config' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create default accounts after creating the company and database
        Company::createDefaultAccounts($cm_pid, $db_name, auth()->id());

        // return back()->with('message', SessionMessageHelper::COMPANY_CREATED);
        // return redirect()->route('companies.index')->with('message', SessionMessageHelper::COMPANY_CREATED);
        return redirect()->route('companies.index')->with('success', 'New company is successfully created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $primaryDB = env('DB_DATABASE');

        // Get company from primary database
        $company = DB::table($primaryDB . '.companies')
            ->where('id', $id)
            ->first();

        if (!$company) {
            return back()->with('error', 'Sorry! A company not found.');
        }

        // Get tenant information
        $tenant = DB::table($primaryDB . '.tenants')
            ->where('company_id', $id)
            ->first();

        return view('admin.company.show', [
            'company' => $company,
            'tenant' => $tenant,
            // 'images' => $company->images // Uncomment if you implement images
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $primaryDB = env('DB_DATABASE');

        $company = DB::table($primaryDB . '.companies')
            ->where('id', $id)
            ->first();

        if (!$company) {
            return back()->with('error', 'Sorry! A company not found.');
        }

        // Get the tenant information
        $tenant = DB::table($primaryDB . '.tenants')
            ->where('company_id', $id)
            ->first();

        return view('admin.company.edit', ['company' => $company, 'tenant' => $tenant]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $primaryDB = env('DB_DATABASE') ?? null;

        // Find the company in the primary database
        $company = DB::table($primaryDB . '.companies')->where('id', $id)->first();

        if (!$company) {
            return back()->with('error', 'Company not found!');
        }

        // Validate contact number uniqueness (excluding current company)
        $existingCompany = DB::table($primaryDB . '.companies')
            ->where('contact_no', $request->contact_no)
            ->where('id', '!=', $id)
            ->first();

        if ($existingCompany) {
            return back()->with('error', 'Sorry! A company already registered with this phone number.');
        }

        // Update company in primary database
        DB::table($primaryDB . '.companies')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'region' => $request->region,
                'office_address' => $request->office_address,
                'contact_no' => $request->contact_no,
                'email' => $request->email,
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        // Update tenant in primary database
        DB::table($primaryDB . '.tenants')
            ->where('company_id', $id)
            ->update([
                'name' => $request->name,
                'updated_at' => now(),
            ]);

        // Update company in tenant database
        DB::table($company->db_name . '.companies')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'region' => $request->region,
                'office_address' => $request->office_address,
                'contact_no' => $request->contact_no,
                'email' => $request->email,
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        return redirect()->route('companies.index')->with('success', 'Company details updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($companyId)
    {
        $primaryDB = env('DB_DATABASE');

        // Update in primary database
        $company = DB::table($primaryDB . '.companies')
            ->where('id', $companyId)
            ->firstOrFail();

        DB::table($primaryDB . '.companies')
            ->where('id', $companyId)
            ->update(['status' => 'INACTIVE']);

        // Update in company's own database
        DB::table($company->db_name . '.companies')
            ->where('id', $companyId)
            ->update(['status' => 'INACTIVE']);

        return back()->with("success", $company->name . " deactivated successfully!");
    }

    public function getUsers($companyId, Request $request)
    {
        $primaryDB = env('DB_DATABASE');
        $perPage = 10;
        $search = $request->input('search', '');

        $query = DB::table($primaryDB . '.users')
            ->whereNull('tenant_id')
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'links' => $users->linkCollection()->toArray()
        ]);
    }

    public function assignUser(Request $request, $companyId)
    {
        $primaryDB = env('DB_DATABASE');

        DB::beginTransaction();

        try {
            $company = DB::table($primaryDB . '.companies')->find($companyId);
            $user = DB::table($primaryDB . '.users')
                ->where('id', $request->user_id)
                ->whereNull('tenant_id')
                ->first();

            if (!$company || !$user) {
                throw new \Exception('Company or user not found');
            }

            // Assign user in primary DB
            DB::table($primaryDB . '.users')
                ->where('id', $user->id)
                ->update(['tenant_id' => $companyId]);

            // Insert user into company DB
            DB::table($company->db_name . '.users')->insert([
                'id' => $user->id,
                'tenant_id' => $companyId,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => $user->password,
                'status' => $user->status,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'User assigned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function companyUsers($companyId)
    {
        $primaryDB = env('DB_DATABASE');

        $company = DB::table($primaryDB . '.companies')->find($companyId);
        if (!$company) {
            return back()->with('error', 'Company not found');
        }

        $users = DB::table($primaryDB . '.users')
            ->where('tenant_id', $companyId)
            ->select('id', 'name', 'email', 'phone', 'status', 'created_at')
            ->orderBy('name')
            ->get();

        return view('admin.company.users', [
            'company' => $company,
            'users' => $users
        ]);
    }

    public function removeUser($companyId, $userId)
    {
        $primaryDB = env('DB_DATABASE');

        DB::beginTransaction();

        try {
            $company = DB::table($primaryDB . '.companies')->find($companyId);
            $user = DB::table($primaryDB . '.users')->find($userId);

            if (!$company || !$user) {
                throw new \Exception('Company or user not found');
            }

            // Reset tenant_id in primary DB
            DB::table($primaryDB . '.users')
                ->where('id', $userId)
                ->update(['tenant_id' => null]);

            // Update status to inactive in company DB
            DB::table($company->db_name . '.users')
                ->where('id', $userId)
                ->update(['status' => 'INACTIVE']);

            DB::commit();

            return redirect()->back()->with('success', 'User removed from company successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show company profile (for company admin)
     */
    public function companyProfile()
    {
        $primaryDB = env('DB_DATABASE') ?? null;
        $companyId = Auth::user()->tenant_id;

        $company = DB::table($primaryDB . '.companies')
            ->where('id', $companyId)
            ->first();

        // Get logo if exists
        $logo = DB::table($company->db_name . '.images')
            ->where('imageable_type', 'App\Models\Company')
            ->where('imageable_id', $companyId)
            ->first();

        return view('admin.company.profile.show', [
            'company' => $company,
            'logo' => $logo
        ]);
    }

    /**
     * Show edit form for company profile
     */
    public function editCompanyProfile()
    {
        $primaryDB = env('DB_DATABASE') ?? null;
        $companyId = Auth::user()->tenant_id;

        $company = DB::table($primaryDB . '.companies')
            ->where('id', $companyId)
            ->first();

        // Get logo if exists
        $logo = DB::table($company->db_name . '.images')
            ->where('imageable_type', 'App\Models\Company')
            ->where('imageable_id', $company->id)
            ->first();

        return view('admin.company.profile.edit', [
            'company' => $company,
            'logo' => $logo
        ]);
    }

    /**
     * Update company profile
     */
    public function updateCompanyProfile(Request $request)
    {
        $primaryDB = env('DB_DATABASE') ?? null;
        $companyId = Auth::user()->tenant_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_no' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'office_address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $company = DB::table($primaryDB . '.companies')
            ->where('id', $companyId)
            ->first();

        DB::beginTransaction();

        try {
            // Update in landlord database
            DB::table($primaryDB . '.companies')
                ->where('id', $companyId)
                ->update([
                    'name' => $request->name,
                    'contact_no' => $request->contact_no,
                    'email' => $request->email,
                    'office_address' => $request->office_address,
                    'updated_at' => now(),
                ]);

            // Update in company's own database
            DB::table($company->db_name . '.companies')
                ->where('id', $companyId)
                ->update([
                    'name' => $request->name,
                    'contact_no' => $request->contact_no,
                    'email' => $request->email,
                    'office_address' => $request->office_address,
                    'updated_at' => now(),
                ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $extension = $image->getClientOriginalExtension();
                $imageName = 'company-logo-' . $company->id . '-' . time() . '.' . $extension;
                $path = $image->storeAs('company-logos', $imageName, 'public');

                // Delete old logo if exists
                DB::table($company->db_name . '.images')
                    ->where('imageable_type', 'App\Models\Company')
                    ->where('imageable_id', $company->id)
                    ->delete();

                // Insert new logo
                DB::table($company->db_name . '.images')->insert([
                    'imageable_type' => 'App\Models\Company',
                    'imageable_id' => $company->id,
                    'path' => $path,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('company.profile')->with('success', 'Company profile updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update company profile: ' . $e->getMessage());
        }
    }
}
