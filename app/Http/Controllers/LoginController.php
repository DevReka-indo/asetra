<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'credential' => 'required|string',
            'password'   => 'required|string',
        ]);

        $user = User::where('email', $request->credential)
                    ->orWhere('nip', $request->credential)
                    ->first();

        if (!$user) {
            return back()->withErrors([
                'credential' => 'Akun tidak terdaftar.',
            ])->onlyInput('credential');
        }

        $authenticated = false;
        if (Hash::check($request->password, $user->password)) {
            $authenticated = true;
        } else {
            // Coba autentikasi menggunakan SIPO (API Eksternal)
            $baseUrl = rtrim(config('services.external_api.base_url'), '/');
            if ($baseUrl) {
                try {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'Accept' => 'application/json',
                    ])->timeout(5)->post("{$baseUrl}/login", [
                        'login' => $user->nip ?: $request->credential,
                        'password' => $request->password,
                    ]);

                    if ($response->successful()) {
                        $responseData = $response->json();
                        if (!empty($responseData['token'])) {
                            // Autentikasi SIPO berhasil
                            // Simpan password secara lokal untuk login berikutnya
                            $user->password = Hash::make($request->password);
                            $user->save();
                            $authenticated = true;
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("SIPO auth failed for user NIP: {$user->nip}. Error: " . $e->getMessage());
                }
            }
        }

        if (!$authenticated) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah.',
            ])->onlyInput('credential'); 
        }

        // Login berhasil
        Auth::login($user, $request->has('remember'));

        $request->session()->regenerate();

        if (!$user->role) {
            Auth::logout();
            return back()->withErrors(['credential' => 'Akun ini tidak memiliki role.']);
        }

        $roleName = strtolower($user->role->nm_role);

        return redirect()->route($roleName . '.dashboard');
    }

    

    /**
     * Logout user
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}