<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Show the user profile
     */
    public function show()
    {
        $user = auth()->user();
        return view('admin.profile.show', compact('user'));
    }

    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|different:old_password',
            'confirm_new_password' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->old_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['old_password' => 'Current password is incorrect']);
        }

        // Update password in both landlord and tenant databases
        try {

            $hashedPassword = Hash::make($request->new_password);

            // Update landlord DB
            DB::connection('landlord')->table('users')
                ->where('id', $user->id)
                ->update(['password' => $hashedPassword]);

            // Update tenant DB
            DB::connection('tenant')->table('users')
                ->where('id', $user->id)
                ->update(['password' => $hashedPassword]);

            return redirect()->back()
                ->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Password update failed. Please try again.']);
        }
    }
}
