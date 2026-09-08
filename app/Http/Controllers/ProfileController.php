<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|max:30',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        if ($request->filled('new_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match.']);
            }
            $user->password = Hash::make($request->new_password);

            // Log Password Update Audit Event
            AuditLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'event' => 'password_updated',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;

        if ($request->hasFile('profile_photo')) {
            $user->profile_photo = $request->file('profile_photo')->store('profiles', 'public');
        }

        $user->save();

        return back()->with('success', 'Profile information updated successfully!');
    }
}
