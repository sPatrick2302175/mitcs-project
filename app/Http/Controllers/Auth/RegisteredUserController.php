<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Basic input validation
        $request->validate([
            'employee_id_number' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        // 2. Strict Check: Does this employee actually exist in our government records?
        $employee = Employee::where('employee_id_number', $request->employee_id_number)
                            ->where('last_name', $request->last_name)
                            ->first();

        if (!$employee) {
            throw ValidationException::withMessages([
                'employee_id_number' => 'The provided Employee ID and Last Name do not match our records.',
            ]);
        }

        // 3. Strict Check: Has this employee already created an account?
        $alreadyRegistered = User::where('employee_id', $employee->id)->exists();
        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'employee_id_number' => 'An account has already been registered for this Employee ID.',
            ]);
        }

        // 4. Everything matches! Create the user account and link it to the employee profile
        $user = User::create([
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $employee->id,
            'is_admin' => false, // New registrations are always regular employees by default
        ]);

        event(new Registered($user));

        //Auth::login($user); auto log in

        return redirect()->route('login')->with('status', 'Account claimed successfully! Please log in with your Employee ID and new password.');
    }
}
