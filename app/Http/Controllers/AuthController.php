<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()    { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $r)
    {
        $data = $r->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // FIX: this controller replaced Laravel's built-in LoginRequest, which
        // meant it also dropped the built-in login throttling — passwords were
        // brute-forceable at full speed. Throttle per email+IP.
        $key = Str::transliterate(Str::lower($data['email']) . '|' . $r->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Try again in {$seconds} seconds.",
            ]);
        }

        if (!Auth::attempt($data, $r->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'Those details do not match our records.'])
                         ->onlyInput('email');
        }

        if (Auth::user()->status === 'deactivated') {
            Auth::logout();
            $r->session()->invalidate();
            $r->session()->regenerateToken();
            return back()->withErrors(['email' => 'This account has been deactivated.']);
        }

        RateLimiter::clear($key);
        $r->session()->regenerate();

        return redirect()->intended(Auth::user()->isAdmin() ? '/admin' : '/');
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone'     => ['required', 'string', 'max:40'],
            'city'      => ['nullable', 'string', 'max:120'],
            'company'   => ['nullable', 'string', 'max:120'],
            'password'  => ['required', 'confirmed', Password::min(8)],
            // FIX: constrain the accepted image types rather than trusting
            // whatever the "image" rule allows (which includes SVG).
            'doc_front' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic', 'max:5120'],
            'doc_back'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic', 'max:5120'],
        ]);

        // ─────────────────────────────────────────────────────────────
        // FIX — THE BIG ONE.
        // These were stored on the 'public' disk and rendered with
        // Storage::url(), which put every uploaded driver's licence and
        // passport at an unauthenticated public URL after storage:link.
        // They now go on the private 'local' disk and are served only
        // through AdminController::kycDocument(), behind auth+admin.
        // ─────────────────────────────────────────────────────────────
        $data['doc_front'] = $r->file('doc_front')->store('kyc', config('filesystems.private_disk'));

        $data['doc_back'] = $r->hasFile('doc_back')
            ? $r->file('doc_back')->store('kyc', config('filesystems.private_disk'))
            : null;

        $data['is_business'] = $r->boolean('is_business');
        $data['source']      = 'Direct';
        $data['status']      = 'pending';   // admin must approve
        $data['verified']    = false;
        $data['role']        = 'bidder';    // never take this from input

        $user = User::create($data);
        Auth::login($user);
        $r->session()->regenerate();

        return redirect('/')->with('ok',
            'Registration received. An agent will verify your ID before bidding unlocks.');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/');
    }
}
