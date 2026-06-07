<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\DataAset;
use App\Models\AsetFoto;
use App\Models\KategoriAset;
use App\Models\LokasiAset;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DataAsetApiController extends BaseApiController
{
    use \App\Traits\HandlesImageUploads;
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $kondisi = $request->input('kondisi');
        $status  = $request->input('status_aset');
        $lokasiId = $request->input('lokasi_id');
        $departmentId = $request->input('department_id');
        $divisiId = $request->input('divisi_id');

        $query = DataAset::with(['kategoriAset', 'director', 'divisi', 'department', 'section', 'unit', 'lokasi', 'pic', 'foto']);

        // Filter based on user role scope if not superadmin/GA
        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        if (!$isAdmin) {
            if ($user->unit_id_unit) {
                $query->where('id_unit', $user->unit_id_unit);
            } elseif ($user->section_id_section) {
                $query->where('id_section', $user->section_id_section);
            } elseif ($user->department_id_department) {
                $query->where('id_department', $user->department_id_department);
            } elseif ($user->divisi_id_divisi) {
                $query->where('id_divisi', $user->divisi_id_divisi);
            } elseif ($user->director_id_director) {
                $query->where('id_director', $user->director_id_director);
            } else {
                $query->where('id', 0);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_aset', 'LIKE', "%{$search}%")
                  ->orWhere('nama_aset', 'LIKE', "%{$search}%")
                  ->orWhereHas('kategoriAset', function($qj) use ($search) {
                      $qj->where('nama', 'LIKE', "%{$search}%")
                         ->orWhere('kode', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($kondisi) {
            $query->where('status_kondisi', $kondisi);
        }

        if ($status) {
            $query->where('status_aset', $status);
        }

        if ($lokasiId) {
            $query->where('lokasi_id', $lokasiId);
        }

        if ($departmentId) {
            $query->where('id_department', $departmentId);
        }

        if ($divisiId) {
            $query->where('id_divisi', $divisiId);
        }

        $asets = $query->latest()->paginate($perPage);

        return $this->success($asets, 'Data aset retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_aset'            => 'required|string|max:150',
            'kategori_id'          => 'required|integer|exists:kategori_aset,id',
            'kode_organisasi'      => 'required|string',
            'lokasi_id'            => 'required|integer|exists:lokasi_aset,lokasi_id',
            'merek'                => 'required|string|max:100',
            'deskripsi'            => 'required|string',
            'tanggal_kapitalisasi' => 'required|date',
            'pic_id'               => 'required|integer|exists:users,id',
            'penanggung_jawab_id'  => 'required|integer|exists:users,id',
            'bast'                 => 'nullable|string|max:255',
            'status_kondisi'       => 'required|in:Baik,Rusak,Bongkar,Tidak Terpakai,Hilang,Tidak Teridentifikasi',
            'status_aset'          => 'required|in:Aktif,Tidak Aktif,Dalam Perbaikan,Dipinjam,Hilang',
            'keterangan'           => 'nullable|string',
            'foto'                 => 'required|array|min:1|max:10',
            'foto.*'               => 'image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $data = $request->only(
            'nama_aset', 'kategori_id', 'lokasi_id', 'merek', 'deskripsi',
            'tanggal_kapitalisasi', 'pic_id', 'penanggung_jawab_id', 'bast',
            'status_kondisi', 'status_aset', 'keterangan'
        );

        if ($request->has('kode_organisasi')) {
            $parts = explode('_', $request->kode_organisasi);
            if (count($parts) === 2) {
                $type = $parts[0];
                $id = $parts[1];
                $data["id_{$type}"] = $id;
            }
        }

        $aset = DataAset::create($data);

        // Upload photos
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $i => $file) {
                $savedPath = $this->compressAndStore($file, 'dokumentasi_aset');
                
                if ($savedPath) {
                    AsetFoto::create([
                        'aset_id'   => $aset->id,
                        'path_foto' => $savedPath,
                        'urutan'    => $i + 1,
                    ]);
                }
            }
        }

        $aset->load(['kategoriAset', 'lokasi', 'foto']);

        return $this->success($aset, 'Aset berhasil ditambahkan dengan Nomor: ' . $aset->nomor_aset, 210);
    }

    public function show($id)
    {
        $aset = DataAset::with([
            'kategoriAset',
            'director', 'divisi', 'department', 'section', 'unit',
            'lokasi',
            'pic',
            'penanggungJawab',
            'foto',
            'logAset' => fn($q) => $q->latest('tanggal_cek')->limit(10),
        ])->findOrFail($id);

        $user = auth()->user();
        $isAdmin = $user->role_id_role == 1 || $user->isBagianUmum();

        if (!$isAdmin) {
            $isAuthorized = false;
            if ($user->unit_id_unit && $aset->id_unit == $user->unit_id_unit) $isAuthorized = true;
            elseif ($user->section_id_section && $aset->id_section == $user->section_id_section) $isAuthorized = true;
            elseif ($user->department_id_department && $aset->id_department == $user->department_id_department) $isAuthorized = true;
            elseif ($user->divisi_id_divisi && $aset->id_divisi == $user->divisi_id_divisi) $isAuthorized = true;
            elseif ($user->director_id_director && $aset->id_director == $user->director_id_director) $isAuthorized = true;
            elseif ($aset->pic_id == $user->id) $isAuthorized = true;

            if (!$isAuthorized) {
                return $this->error('Anda tidak memiliki akses untuk melihat detail aset dari departemen lain.', 403);
            }
        }

        return $this->success($aset, 'Detail data aset retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $aset = DataAset::findOrFail($id);

        $request->validate([
            'nama_aset'            => 'required|string|max:150',
            'kategori_id'          => 'required|integer|exists:kategori_aset,id',
            'kode_organisasi'      => 'required|string',
            'lokasi_id'            => 'required|integer|exists:lokasi_aset,lokasi_id',
            'merek'                => 'required|string|max:100',
            'deskripsi'            => 'required|string',
            'tanggal_kapitalisasi' => 'required|date',
            'pic_id'               => 'required|integer|exists:users,id',
            'penanggung_jawab_id'  => 'required|integer|exists:users,id',
            'bast'                 => 'nullable|string|max:255',
            'status_kondisi'       => 'required|in:Baik,Rusak,Bongkar,Tidak Terpakai,Hilang,Tidak Teridentifikasi',
            'status_aset'          => 'required|in:Aktif,Tidak Aktif,Dalam Perbaikan,Dipinjam,Hilang',
            'keterangan'           => 'nullable|string',
            'foto_baru'            => 'nullable|array|max:10',
            'foto_baru.*'          => 'image|mimes:jpeg,png,jpg|max:4096',
            'hapus_foto'           => 'nullable|array',
            'hapus_foto.*'         => 'integer|exists:aset_foto,id',
        ]);

        $data = $request->only(
            'nama_aset', 'kategori_id', 'lokasi_id', 'merek', 'deskripsi',
            'tanggal_kapitalisasi', 'pic_id', 'penanggung_jawab_id', 'bast',
            'status_kondisi', 'status_aset', 'keterangan'
        );

        if ($request->has('kode_organisasi')) {
            $parts = explode('_', $request->kode_organisasi);
            if (count($parts) === 2) {
                $type = $parts[0];
                $orgId = $parts[1];

                // Reset existing organization structure
                $aset->id_director = null;
                $aset->id_divisi = null;
                $aset->id_department = null;
                $aset->id_section = null;
                $aset->id_unit = null;

                $data["id_{$type}"] = $orgId;
            }
        }

        $aset->update($data);

        // Delete specified photos
        if ($request->has('hapus_foto')) {
            foreach ($request->hapus_foto as $fotoId) {
                $foto = AsetFoto::find($fotoId);
                if ($foto && $foto->aset_id === $aset->id) {
                    if (!filter_var($foto->path_foto, FILTER_VALIDATE_URL)) {
                        Storage::disk('public')->delete($foto->path_foto);
                    }
                    $foto->delete();
                }
            }
        }

        // Upload new photos
        if ($request->hasFile('foto_baru')) {
            $urutanTerakhir = $aset->foto()->max('urutan') ?? 0;
            foreach ($request->file('foto_baru') as $i => $file) {
                $savedPath = $this->compressAndStore($file, 'dokumentasi_aset');

                if ($savedPath) {
                    AsetFoto::create([
                        'aset_id'   => $aset->id,
                        'path_foto' => $savedPath,
                        'urutan'    => $urutanTerakhir + $i + 1,
                    ]);
                }
            }
        }

        $aset->load(['kategoriAset', 'lokasi', 'foto']);

        return $this->success($aset, 'Data aset berhasil diperbarui!');
    }

    public function destroy(Request $request, $id)
    {
        $aset = DataAset::findOrFail($id);

        $request->validate([
            'dokumen_penghapusan' => 'required|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('dokumen_penghapusan')) {
            $path = $request->file('dokumen_penghapusan')->store('dokumen_penghapusan', 'public');
            $aset->dokumen_penghapusan = $path;
            $aset->save();
        }

        $aset->delete();

        return $this->success(null, 'Data aset berhasil dihapus (Soft Delete).');
    }

    /**
     * Print PDF labels for selected assets
     */
    public function cetakLabel(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:data_aset,id'
        ]);

        $asets = DataAset::with(['kategoriAset', 'lokasi'])->whereIn('id', $request->ids)->get();

        if ($asets->isEmpty()) {
            return $this->error('Aset tidak ditemukan.', 404);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('aset.print_label_pdf', compact('asets'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream('Label_Aset_Selected.pdf');
    }

    /**
     * Print PDF labels for all assets in a location
     */
    public function cetakLabelLokasi($lokasi_id)
    {
        $asets = DataAset::with(['kategoriAset', 'lokasi'])
            ->where('lokasi_id', $lokasi_id)
            ->get();
            
        if ($asets->isEmpty()) {
            return $this->error('Tidak ada aset di ruangan/lokasi ini.', 404);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('aset.print_label_pdf', compact('asets'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream("Label_Aset_Lokasi_{$lokasi_id}.pdf");
    }
}
