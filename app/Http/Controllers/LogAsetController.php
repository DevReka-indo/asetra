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
    use \App\Traits\HandlesImageUploads;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy  = $request->input('sort_by', 'created_at');
        $orderBy = $request->input('order_by', 'desc');

        $allowedSortColumns = ['created_at', 'tanggal_cek', 'kondisi', 'nama_aset', 'nama_lokasi', 'dicatat_oleh_name'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        if (!in_array($orderBy, ['asc', 'desc'])) {
            $orderBy = 'desc';
        }

        $query = LogAset::with(['aset', 'dicatatOleh', 'lokasi', 'director', 'divisi', 'department', 'section', 'unit']);

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
        if ($request->has('kondisi') && $request->kondisi != '') {
            $query->where('log_aset.kondisi', $request->kondisi);
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
            $query->where('log_aset.status_aset', $request->status_aset);
        }

        // Filter by lokasi if provided
        $lokasiId = $request->input('lokasi');
        if (!$isAdmin) {
            $lokasiId = null;
        }
        if ($lokasiId != '') {
            $query->where('log_aset.lokasi_id', $lokasiId);
        }

        // Apply sorting
        if ($sortBy === 'nama_aset') {
            $query->leftJoin('data_aset', 'log_aset.aset_id', '=', 'data_aset.id')
                  ->select('log_aset.*')
                  ->orderBy('data_aset.nama_aset', $orderBy);
        } elseif ($sortBy === 'dicatat_oleh_name') {
            $query->leftJoin('users', 'log_aset.dicatat_oleh', '=', 'users.id')
                  ->select('log_aset.*')
                  ->orderBy('users.firstname', $orderBy);
        } elseif ($sortBy === 'nama_lokasi') {
            $query->leftJoin('lokasi_aset', 'log_aset.lokasi_id', '=', 'lokasi_aset.lokasi_id')
                  ->select('log_aset.*')
                  ->orderBy('lokasi_aset.nama_lokasi', $orderBy);
        } else {
            $query->orderBy('log_aset.' . $sortBy, $orderBy);
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
            $logData['foto_bukti'] = $this->compressAndStore($request->file('foto_bukti'), 'log_aset');
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
