<?php

namespace App\Http\Requests\Auth;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'employee_id_number' => ['required', 'string'],// validation rule
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Find the official employee record matching the submitted ID number
        $employee = Employee::where('employee_id_number', $this->employee_id_number)->first();

        // Locate the web account associated with that employee profile
        $user = $employee ? User::where('employee_id', $employee->id)->first() : null;

        // If the user doesn't exist, we use a dummy hash string 
        // to force Hash::check to perform the full computation anyway.
        // makes valid and invalid ID attempts take the exact same amount of time.
        $dummyHash = '$2y$10$I95vA68mU5h0tYxqy9.wS.7P9P4XuxYm4uA6i/4eNlyB2hWjCba6K'; 
        $userHash = $user ? $user->password : $dummyHash;

        // Perform the password verification check safely
        if (! $user || ! Hash::check($this->password, $userHash)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'employee_id_number' => __('auth.failed'),
            ]);
        }

        // Log the user in directly since we already have the object
        // and verified the password, saving a duplicate database query.
        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'employee_id_number' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('employee_id_number')).'|'.$this->ip());
    }
}