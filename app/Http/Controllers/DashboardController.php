<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        \Log::info('Dashboard Access', [
            'user_id' => $user->id,
            'role_id_role' => $user->role_id_role,
            'section_id_section' => $user->section_id_section,
        ]);

        // GA dashboard
        if ($user->section_id_section == 12) {
            return redirect()->route('general-affairs.dashboard');
        }

        // Role check untuk dashboard umum
        if ($user->role_id_role == 1) {
            return view('superadmin.dashboard');
        } elseif ($user->role_id_role == 3) {
            // Role 3 adalah Manager
            return view('manager.dashboard');
        } else {
            // Role 2 dan Role lainnya (Staff)
            return view('staff.dashboard');
        }
    }

    public function generalAffairsDashboard()
    {
        $user = Auth::user();
        
        // lihat data user
        \Log::info('GA Dashboard Access', [
            'user_id' => $user->id,
            'user_fullname' => $user->firstname . ' ' . $user->lastname,
            'role_id_role' => $user->role_id_role,
            'section_id_section' => $user->section_id_section,
            'department_id_department' => $user->department_id_department,
        ]);

        // Allow hanya jika section 12 (General Affairs staff)
        if ($user->section_id_section != 12) {
            abort(403, 'Akses dashboard bagian umum hanya untuk staff bagian umum. Role: ' . $user->role_id_role . ', Section: ' . $user->section_id_section);
        }

        return view('general-affairs.dashboard');
    }
}