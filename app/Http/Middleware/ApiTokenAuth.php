<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?: $request->input('api_token');
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak ditemukan. Silakan gunakan header Authorization Bearer.'
            ], 401);
        }

        try {
            // Decrypt the token securely using Laravel's Crypt facade
            $payload = json_decode(Crypt::decryptString($token), true);
            
            if (!isset($payload['id']) || !isset($payload['created_at'])) {
                throw new \Exception('Payload token tidak valid.');
            }

            // Token expires after 7 days
            if (now()->timestamp - $payload['created_at'] > 86400 * 7) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token telah kedaluwarsa. Silakan login kembali.'
                ], 401);
            }

            $user = User::find($payload['id']);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan.'
                ], 401);
            }

            // Log the user in for the current request context
            Auth::login($user);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid.'
            ], 401);
        }

        return $next($request);
    }
}
