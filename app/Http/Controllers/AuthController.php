<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            if ($user->isAdmin()) {
                return redirect()->intended('admin/dashboard');
            } elseif ($user->isDoctor()) {
                return redirect()->intended('doctor/dashboard');
            } elseif ($user->isPatient()) {
                return redirect()->intended('patient/dashboard');
            } else {
                return redirect()->intended('staff/dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        $departments = Department::all();
        return view('auth.register', compact('departments'));
    }

    /**
     * Handle a registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $role = $request->input('role', 'patient');
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone_number' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'address' => ['required', 'string'],
        ]);
        
        if ($role === 'doctor') {
            $validator->addRules([
                'department_id' => ['required', 'exists:departments,id'],
                'specialization' => ['required', 'string', 'max:255'],
                'qualification' => ['required', 'string'],
                'experience' => ['required', 'integer', 'min:0'],
                'license_number' => ['required', 'string', 'max:50', 'unique:doctors'],
            ]);
        }
        
        $validator->validate();
        
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);
        
        // Create profile based on role
        if ($role === 'patient') {
            Patient::create([
                'user_id' => $user->id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'medical_history' => $request->medical_history,
                'allergies' => $request->allergies,
            ]);
        } elseif ($role === 'doctor') {
            Doctor::create([
                'user_id' => $user->id,
                'department_id' => $request->department_id,
                'specialization' => $request->specialization,
                'qualification' => $request->qualification,
                'experience' => $request->experience,
                'license_number' => $request->license_number,
                'consultation_fee' => $request->consultation_fee ?? 0,
                'bio' => $request->bio,
            ]);
        }
        
        // Log the user in
        Auth::login($user);
        
        // Redirect based on role
        if ($role === 'doctor') {
            return redirect()->route('doctor.dashboard');
        } else {
            return redirect()->route('patient.dashboard');
        }
    }

    /**
     * Log the user out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Display the password reset request view.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle a password reset request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // In a real application, you would send a password reset link
        // For this demo, we'll just redirect with a success message
        
        return back()->with('status', 'Password reset link sent to your email!');
    }

    /**
     * Display the password reset view.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle a password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // In a real application, you would reset the password
        // For this demo, we'll just redirect with a success message
        
        return redirect()->route('login')->with('status', 'Your password has been reset!');
    }
}
