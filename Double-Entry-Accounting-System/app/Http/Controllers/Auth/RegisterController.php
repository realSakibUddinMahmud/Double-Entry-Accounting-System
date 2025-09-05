<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use DB;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    // protected function create(array $data)
    // {
    //     // return User::create([
    //     //     'name' => $data['name'],
    //     //     'email' => $data['email'],
    //     //     'phone' => $data['phone'],
    //     //     'tenant_id' => Tenant::current()?->id ?? null, // Assuming tenant_id is nullable and not set during registration
    //     //     'status' => true, // Default status set to true
    //     //     'password' => Hash::make($data['password']),
    //     // ]);

    //     $timestamp = now();

    //     // First, insert into tenant DB (this is your main user creation)
    // $tenantUser = User::create([
    //     'name' => $data['name'],
    //     'email' => $data['email'],
    //     'phone' => $data['phone'],
    //     'tenant_id' => Tenant::current()?->company_id ?? null,
    //     'status' => true,
    //     'password' => Hash::make($data['password']),
    //     'created_at' => $timestamp,
    //     'updated_at' => $timestamp,
    // ]);

    // // Then, also insert into landlord DB
    // DB::connection('landlord')->table('users')->insert([
    //     'name' => $tenantUser->name,
    //     'email' => $tenantUser->email,
    //     'phone' => $tenantUser->phone,
    //     'tenant_id' => Tenant::current()?->company_id ?? null,
    //     'status' => $tenantUser->status,
    //     'password' => $tenantUser->password,
    //     'created_at' => $timestamp,
    //     'updated_at' => $timestamp,
    // ]);

    //     return $tenantUser;
    // }

    protected function create(array $data)
    {
        DB::connection('landlord')->beginTransaction();
        DB::beginTransaction(); // Tenant transaction

        try {
            $timestamp = now();
            $tenantId = Tenant::current()?->company_id ?? null;

            // 1. First insert into landlord
            $userId = DB::connection('landlord')->table('users')->insertGetId([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'tenant_id' => $tenantId,
                'status' => true,
                'password' => Hash::make($data['password']),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
            $landlordUser = DB::connection('landlord')
                ->table('users')
                ->where('id', $userId)
                ->first();

            $userId = $landlordUser->id;
            // 2. If tenant exists, insert into tenant with same ID
            if ($tenantId) {
                $tenantUser = User::create([
                    'id' => $userId, // Ensure the ID matches the landlord's
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'tenant_id' => $tenantId,
                    'status' => true,
                    'password' => Hash::make($data['password']),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
                DB::commit(); // Only commit tenant transaction if created
            } else {
                // If no tenant, just create in landlord
                $tenantUser = $landlordUser;
            }

            DB::connection('landlord')->commit(); // Always commit landlord

            return $tenantUser;
        } catch (\Exception $e) {
            DB::connection('landlord')->rollBack();
            DB::rollBack();
            throw $e;
        }
    }
}
