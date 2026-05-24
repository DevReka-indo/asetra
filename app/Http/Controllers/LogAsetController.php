<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LogAset;
use App\Models\DataAset;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogAsetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LogAset::with(['aset', 'dicatatOleh', 'lokasi', 'director', 'divisi', 'department', 'section', 'unit'])
                    ->latest('created_at');

        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        if (!$isAdmin) {
            $query->whereHas('aset', function($q) use ($user) {
                $q->where(function($subQ) use ($user) {
                    if ($user->unit_id_unit) {
                        $subQ->where('id_unit', $user->unit_id_unit);
                    } elseif ($user->section_id_section) {
                        $subQ->where('id_section', $user->section_id_section);
                    } elseif ($user->department_id_department) {
                        $subQ->where('id_department', $user->department_id_department);
                    } elseif ($user->divisi_id_divisi) {
                        $subQ->where('id_divisi', $user->divisi_id_divisi);
                    } elseif ($user->director_id_director) {
                        $subQ->where('id_director', $user->director_id_director);
                    }
                    
                    $subQ->orWhere('pic_id', $user->id);
                });
            });
        }

        // Filter
        if ($request->has('condition') && $request->condition != '') {
            $query->where('kondisi', $request->condition);
        }

        // Search by aset nomor or nama
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->whereHas('aset', function($q) use ($searchTerm) {
                $q->where('nomor_aset', 'like', "%{$searchTerm}%")
                  ->orWhere('nama_aset', 'like', "%{$searchTerm}%");
            });
        }

        // Filter
        if ($request->has('status_aset') && $request->status_aset != '') {
            $query->where('status_aset', $request->status_aset);
        }

        // Filter by lokasi if provided
        if ($request->has('lokasi') && $request->lokasi != '') {
            $query->where('lokasi_id', $request->lokasi);
        }

        // Pagination
        $perPage = $request->has('per_page') ? (int)$request->per_page : 15;
        $logs = $query->paginate($perPage);

        // Get all lokasi
        $lokasis = \App\Models\LokasiAset::all();
                    
        return view('aset.log_index', compact('logs', 'lokasis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'aset_id'         => 'required|exists:data_aset,id',
            'kondisi'         => 'required|string|max:50',
            'status_aset'     => 'nullable|string|max:50',
            'keterangan'      => 'nullable|string',
            'lokasi_id'       => 'nullable|exists:lokasi_aset,lokasi_id',
            'kode_organisasi' => 'nullable|string',
            'foto_bukti'      => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $logData = [
            'aset_id'      => $request->aset_id,
            'tanggal_cek'  => date('Y-m-d'),
            'kondisi'      => $request->kondisi,
            'status_aset'  => $request->status_aset,
            'keterangan'   => $request->keterangan,
            'lokasi_id'    => $request->lokasi_id,
            'dicatat_oleh' => Auth::id(),
        ];

        $aset = DataAset::findOrFail($request->aset_id);
        
        // Cek perubahan
        $perubahan = [];
        if ($request->kondisi && $aset->status_kondisi != $request->kondisi) {
            $perubahan[] = "Kondisi: " . ($aset->status_kondisi ?: '-') . " ➔ " . $request->kondisi;
        }
        if ($request->status_aset && $aset->status_aset != $request->status_aset) {
            $perubahan[] = "Status: " . ($aset->status_aset ?: '-') . " ➔ " . $request->status_aset;
        }
        if ($request->lokasi_id && $aset->lokasi_id != $request->lokasi_id) {
            $lokasiLama = $aset->lokasi ? $aset->lokasi->nama_lokasi : '-';
            $lokasiBaru = \App\Models\LokasiAset::find($request->lokasi_id);
            $namaLokasiBaru = $lokasiBaru ? $lokasiBaru->nama_lokasi : '-';
            $perubahan[] = "Lokasi: " . $lokasiLama . " ➔ " . $namaLokasiBaru;
        }
        if ($request->kode_organisasi && $aset->kode_organisasi != $request->kode_organisasi) {
            $perubahan[] = "Perpindahan Divisi/Departemen";
        }
        if ($request->kode_organisasi) {
            $parts = explode('_', $request->kode_organisasi);
            if (count($parts) === 2) {
                $type = $parts[0];
                $id = $parts[1];
                
                // assign ke logData
                $logData["id_{$type}"] = $id;

                // Set Aset master target
                $aset->id_director = null;
                $aset->id_divisi = null;
                $aset->id_department = null;
                $aset->id_section = null;
                $aset->id_unit = null;

                $aset->{"id_{$type}"} = $id;
            }
        }

        if ($request->hasFile('foto_bukti')) {
            $logData['foto_bukti'] = $request->file('foto_bukti')->store('log_aset', 'public');
        }

        // Tentukan flag perubahan
        if (count($perubahan) > 0) {
            $logData['flag_perubahan'] = implode(', ', $perubahan);
        } else {
            $logData['flag_perubahan'] = 'Pengecekan Rutin';
        }

        LogAset::create($logData);

        // Update the master asset data
        if ($request->kondisi) $aset->status_kondisi = $request->kondisi;
        if ($request->status_aset) $aset->status_aset = $request->status_aset;
        if ($request->lokasi_id) $aset->lokasi_id = $request->lokasi_id;

        $aset->save();

        // Notify if there is a change
        if (count($perubahan) > 0) {
            $title = 'Update Monitoring Aset';
            $changeStr = implode(', ', $perubahan);
            $message = "Aset {$aset->nama_aset} ({$aset->nomor_aset}) memiliki update monitoring terbaru: {$changeStr}.";
            $url = route('aset.show', $aset->id);
            $type = 'monitoring';

            $recipients = collect();
            if ($aset->pic_id) {
                $pic = User::find($aset->pic_id);
                if ($pic) $recipients->push($pic);
            }
            if ($aset->penanggung_jawab_id) {
                $pj = User::find($aset->penanggung_jawab_id);
                if ($pj) $recipients->push($pj);
            }

            // Notify GA and Superadmin
            $adminGas = User::where('role_id_role', 1)->get()->merge(
                User::all()->filter(fn($u) => $u->isGeneralAffairs())
            );
            $recipients = $recipients->merge($adminGas)->unique('id');

            foreach ($recipients as $recipient) {
                // Don't notify the person who created the log
                if ($recipient->id !== Auth::id()) {
                    $recipient->notify(new SystemNotification($title, $message, $url, $type));
                }
            }
        }

        return back()->with('success', 'Log monitoring aset berhasil ditambahkan!');
    }
}
