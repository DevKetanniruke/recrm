<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo('users.create');
    }

    public function rules(): array
    {
        return [
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'status' => 'required|string|in:Active,Inactive,Suspended',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
