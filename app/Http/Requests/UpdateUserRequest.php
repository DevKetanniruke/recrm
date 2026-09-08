<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo('users.edit');
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'mobile' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string',
            'status' => 'required|string|in:Active,Inactive,Suspended',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
