<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        try {
            // Make API request to login
            $response = Http::post(url('/api/login'), [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store the token in the session
                session(['api_token' => $data['token']]);
                
                // Log the user in locally as well to maintain session
                $user = User::where('email', $request->email)->first();
                
                if (!$user) {
                    // Create a local user record if it doesn't exist
                    $user = User::create([
                        'name' => $data['user']['name'],
                        'email' => $data['user']['email'],
                        'password' => bcrypt(Str::random(16)), // Random password as authentication is via API
                    ]);
                }
                
                Auth::login($user);
                
                return $this->sendLoginResponse($request);
            } else {
                throw ValidationException::withMessages([
                    $this->username() => [$response->json()['message'] ?? 'Invalid credentials'],
                ]);
            }
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                $this->username() => ['Login failed: ' . $e->getMessage()],
            ]);
        }
    }
    
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $token = session('api_token');
        
        if ($token) {
            // Call API logout endpoint
            try {
                Http::withToken($token)->post(url('/api/logout'));
            } catch (\Exception $e) {
                // Log error but continue with local logout
                \Log::error('API logout failed: ' . $e->getMessage());
            }
            
            // Remove token from session
            session()->forget('api_token');
        }
        
        // Perform local logout
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->loggedOut($request) ?: redirect('/');
    }
}
