<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role_id_role == 1) {
            
            return view('superadmin.dashboard');
            
        } elseif ($user->role_id_role == 2) {
            
            return view('admin.dashboard');
            
        } elseif ($user->role_id_role == 3) {
            
            return view('staff.dashboard');
            
        }

        abort(403, 'Akses ke dashboard tidak diizinkan untuk akun ini.');
    }
}