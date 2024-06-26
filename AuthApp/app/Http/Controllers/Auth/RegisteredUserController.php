<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RegisterRequest;


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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {   

        $user = $this->validator($request->all());
        $user = $this->sanitizedInput($request->all());

        if ($user) {
            event(new Registered($user));
            Auth::login($user);
            return redirect(route('dashboard', absolute: false));
        } else {
            // Handle user creation failure
            throw ValidationException::withMessages(['error' => 'User creation failed']);
        }
    }

    /**
     * Sanitize and create a new user.
     *
     * @param array $data
     * @return User|null
     */
    protected function sanitizedInput(array $data): ?User
    {
        $data['name'] = trim($data['name']);
        $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

        if (!empty($data['name']) && !empty($data['email'])) {
            return $this->register($data); // Call the register method with sanitized data
        }

        return null;
    }
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
    /**
     * Create a new user.
     *
     * @param array $data
     * @return User|null
     */
    protected function register(array $data): ?User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
