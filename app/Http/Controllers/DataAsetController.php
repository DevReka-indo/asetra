<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataAset;
use App\Models\AsetFoto;
use App\Models\JenisAsetKhusus;
use App\Models\Divisi;
use App\Models\LokasiAset;
use App\Models\SumberKepemilikan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DataAsetController extends Controller
{
    /**
     * Menampilkan daftar semua aset.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $asets   = DataAset::with(['jenisAsetKhusus', 'divisi', 'lokasi', 'pic', 'fotoPertama'])
                    ->latest()
                    ->paginate($perPage);

        return view('aset.index', compact('asets'));
    }

    /**
     * Menampilkan form untuk membuat aset baru.
     */
    public function create()
    {
        $lastAset = DataAset::orderBy('id', 'desc')->first();
        $nextId   = $lastAset ? str_pad($lastAset->id + 1, 3, '0', STR_PAD_LEFT) : '001';

        $mainDirector = \App\Models\Director::with([
            'subDirectors',
            'divisi.department.section.unit'
        ])->whereNull('parent_director_id')->first();

        $jenisKhusus          = JenisAsetKhusus::all();
        $lokasi               = LokasiAset::all();
        $sumberKepemilikan    = SumberKepemilikan::all();
        $users                = User::all();

        return view('aset.create', compact(
            'mainDirector', 'jenisKhusus', 'lokasi', 'sumberKepemilikan', 'users', 'nextId'
        ));
    }

    /**
     * Menyimpan data aset baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_aset'            => 'required|string|max:150',
            'jenis_aset_khusus_id' => 'required|integer',
            'id_divisi'            => 'required|integer',
            'sumber_kepemilikan_id'=> 'required|integer',
            'lokasi_id'            => 'required|integer',
            'merek'                => 'nullable|string|max:100',
            'deskripsi'            => 'nullable|string',
            'tahun_kapitalisasi'   => 'nullable|integer|min:1900|max:' . date('Y'),
            'pic_id'               => 'nullable|integer',
            'status_kondisi'       => 'required|in:Baik,Rusak,Bongkar,Tidak Terpakai,Hilang,Tidak Teridentifikasi,Lainnya',
            'status_aset'          => 'required|in:Aktif,Tidak Aktif,Dalam Perbaikan,Dipinjam,Hilang',
            'keterangan_kondisi'   => 'nullable|string|max:255',
            'keterangan'           => 'nullable|string',
            // Multi-foto
            'foto'                 => 'nullable|array|max:10',
            'foto.*'               => 'image|mimes:jpeg,png,jpg|max:4096',
        ]);

        $jenis                    = JenisAsetKhusus::find($request->jenis_aset_khusus_id);
        $validatedData['kode_aset'] = $jenis ? $jenis->full_kode : 'XXXX';

        $aset = DataAset::create($validatedData);

        // Simpan foto
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $i => $file) {
                $path = $file->store('dokumentasi_aset', 'public');
                AsetFoto::create([
                    'aset_id'   => $aset->id,
                    'path_foto' => $path,
                    'urutan'    => $i + 1,
                ]);
            }
        }

        return redirect()->route('aset.index')
            ->with('success', 'Aset berhasil ditambahkan dengan Nomor: ' . $aset->nomor_aset);
    }

    /**
     * Menampilkan detail satu aset secara spesifik.
     */
    public function show($id)
    {
        $aset = DataAset::with([
            'jenisAsetKhusus',
            'divisi',
            'lokasi',
            'sumberKepemilikan',
            'pic',
            'foto',
            'logAset' => fn($q) => $q->latest('tanggal_cek')->limit(10),
        ])->findOrFail($id);

        return view('aset.show', compact('aset'));
    }

    /**
     * Menampilkan halaman scanner barcode.
     */
    public function scanner()
    {
        return view('aset.scanner');
    }

    

    /**
     * Menampilkan form untuk mengedit aset.
     */
    public function edit($id)
    {
        $aset = DataAset::with('foto')->findOrFail($id);

        $jenisKhusus       = JenisAsetKhusus::all();
        $divisi            = Divisi::all();
        $lokasi            = LokasiAset::all();
        $sumberKepemilikan = SumberKepemilikan::all();
        $users             = User::all();

        return view('aset.edit', compact(
            'aset', 'jenisKhusus', 'divisi', 'lokasi', 'sumberKepemilikan', 'users'
        ));
    }

    /**
     * Menyimpan perubahan data aset.
     */
    public function update(Request $request, $id)
    {
        $aset = DataAset::findOrFail($id);

        $validatedData = $request->validate([
            'nama_aset'            => 'required|string|max:150',
            'jenis_aset_khusus_id' => 'required|integer',
            'id_divisi'            => 'required|integer',
            'lokasi_id'            => 'required|integer',
            'merek'                => 'nullable|string|max:100',
            'deskripsi'            => 'nullable|string',
            'tahun_kapitalisasi'   => 'nullable|integer|min:1900|max:' . date('Y'),
            'pic_id'               => 'nullable|integer',
            'status_kondisi'       => 'required|in:Baik,Rusak,Bongkar,Tidak Terpakai,Hilang,Tidak Teridentifikasi,Lainnya',
            'keterangan_kondisi'   => 'nullable|string|max:255',
            'keterangan'           => 'nullable|string',
            // Tambah foto baru
            'foto_baru'            => 'nullable|array|max:10',
            'foto_baru.*'          => 'image|mimes:jpeg,png,jpg|max:4096',
            // ID foto yang mau dihapus
            'hapus_foto'           => 'nullable|array',
            'hapus_foto.*'         => 'integer|exists:aset_foto,id',
        ]);

        $aset->update($validatedData);

        // Hapus foto yang diminta
        if ($request->has('hapus_foto')) {
            foreach ($request->hapus_foto as $fotoId) {
                $foto = AsetFoto::find($fotoId);
                if ($foto && $foto->aset_id === $aset->id) {
                    Storage::disk('public')->delete($foto->path_foto);
                    $foto->delete();
                }
            }
        }

        // Tambah foto baru
        if ($request->hasFile('foto_baru')) {
            $urutanTerakhir = $aset->foto()->max('urutan') ?? 0;
            foreach ($request->file('foto_baru') as $i => $file) {
                $path = $file->store('dokumentasi_aset', 'public');
                AsetFoto::create([
                    'aset_id'   => $aset->id,
                    'path_foto' => $path,
                    'urutan'    => $urutanTerakhir + $i + 1,
                ]);
            }
        }

        return redirect()->route('aset.index')
            ->with('success', 'Data aset berhasil diperbarui!');
    }

    /**
     * Menghapus data aset beserta semua foto terkait.
     */
    public function destroy($id)
    {
        $aset = DataAset::with('foto')->findOrFail($id);

        // Hapus semua foto dari storage
        foreach ($aset->foto as $foto) {
            Storage::disk('public')->delete($foto->path_foto);
        }

        // Hapus record aset
        $aset->delete();

        return redirect()->route('aset.index')
            ->with('success', 'Data aset berhasil dihapus!');
    }
}