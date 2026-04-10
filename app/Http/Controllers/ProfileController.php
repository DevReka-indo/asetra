<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Position;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // Tampilkan halaman edit profil
    public function editProfile()
    {
        $user = Auth::user();
        $position = Position::where('id_position', $user->position_id_position)->value('nm_position');
        $orgName = null;
        $director = null;
        if ($user->director_id_director) {
            $director = $user->director->name_director;
            if ($user->divisi_id_divisi) {
                if ($user->department_id_department) {
                    if ($user->section_id_section) {
                        if ($user->unit_id_unit) {
                            $orgName = $user->unit->name_unit;
                        } else {
                            $orgName = $user->section->name_section;
                        }
                    } else {
                        $orgName = $user->department->name_department;
                    }
                } else {
                    $orgName = $user->divisi->nm_divisi;
                }
            }
        }
        return view('edit-profile', compact('user', 'position', 'orgName', 'director'));
    }

    // Simpan atau update profil user
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        try {
            $request->validate([
                'firstname'              => 'required|string|max:50|not_regex:/[\x{1F600}-\x{1F64F}]/u|not_regex:/[\x{1F300}-\x{1F5FF}]/u|not_regex:/[\x{1F680}-\x{1F6FF}]/u|not_regex:/[\x{2600}-\x{26FF}]/u|not_regex:/[\x{2700}-\x{27BF}]/u',
                'lastname'               => 'nullable|string|max:50|not_regex:/[\x{1F600}-\x{1F64F}]/u|not_regex:/[\x{1F300}-\x{1F5FF}]/u|not_regex:/[\x{1F680}-\x{1F6FF}]/u|not_regex:/[\x{2600}-\x{26FF}]/u|not_regex:/[\x{2700}-\x{27BF}]/u',
                //'nip'               => 'required|string|max:255|unique:users,nip,' . $user->id,
                'phone_number'           => 'nullable|string|max:20',
                'password'               => 'nullable|string|min:6|confirmed',
                'password_confirmation'  => 'required_with:password|same:password',
                'profile_image'          => 'nullable|image|max:2048',
            ]);

            

            // Siapkan data yang akan diupdate
            $userData = [
                'firstname'     => $request->firstname,
                'lastname'      => $request->lastname,
                //'nip'      => $request->nip,
                'phone_number'  => $request->phone_number,
            ];

            // Simpan password jika ada input
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            // Simpan gambar profil jika ada file upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $imageData = base64_encode(file_get_contents($file->getRealPath()));
                $userData['profile_image'] = $imageData;
            }

            // Update data user
            User::where('id', $user->id)->update($userData);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
            ]);

            // redirect()
            //     ->route('edit-profile')
            //     ->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            // Kalau ada error, balikin pesan error
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Hapus foto profil
    public function deletePhoto(Request $request)
    {

        $user = Auth::user();
        $user->profile_image = null;
        $user->save();

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }
}
