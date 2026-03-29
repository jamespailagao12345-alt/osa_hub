<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function authenticate(): void
    {
        // Check if user exists and is suspended before attempting authentication
        $user = \App\Models\User::where('email', $this->input('email'))->first();
        
        if ($user && $user->suspended) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact your OSA.',
            ]);
        }
        
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
    }
}