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
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $kondisi = $request->input('kondisi');
        $status  = $request->input('status_aset');

        $query = DataAset::with(['jenisAsetKhusus', 'director', 'divisi', 'department', 'section', 'unit', 'lokasi', 'pic', 'fotoPertama']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_aset', 'LIKE', "%{$search}%")
                  ->orWhere('nama_aset', 'LIKE', "%{$search}%")
                  ->orWhereHas('jenisAsetKhusus', function($qj) use ($search) {
                      $qj->where('jenis_aset', 'LIKE', "%{$search}%")
                         ->orWhere('kode_khusus', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($kondisi) {
            $query->where('status_kondisi', $kondisi);
        }

        if ($status) {
            $query->where('status_aset', $status);
        }

        if ($request->has('lokasi') && $request->input('lokasi') != '') {
            $query->where('lokasi_id', $request->input('lokasi'));
        }

        $asets = $query->latest()
                       ->paginate($perPage)
                       ->withQueryString();

        $lokasis = LokasiAset::all();

        return view('aset.index', compact('asets', 'lokasis'));
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
            'kode_organisasi'      => 'required|string',
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

        if (isset($validatedData['kode_organisasi'])) {
            $parts = explode('_', $validatedData['kode_organisasi']);
            if (count($parts) === 2) {
                $type = $parts[0];
                $id = $parts[1];
                $validatedData["id_{$type}"] = $id;
            }
            unset($validatedData['kode_organisasi']);
        }

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
            'director', 'divisi', 'department', 'section', 'unit',
            'lokasi',
            'sumberKepemilikan',
            'pic',
            'foto',
            'logAset' => fn($q) => $q->latest('tanggal_cek')->limit(10),
        ])->findOrFail($id);

        $mainDirector = \App\Models\Director::with([
            'subDirectors',
            'divisi.department.section.unit'
        ])->whereNull('parent_director_id')->first();

        $lokasi = LokasiAset::all();

        return view('aset.show', compact('aset', 'mainDirector', 'lokasi'));
    }

    /**
     * Menampilkan halaman scanner barcode.
     */
    public function scanner()
    {
        return view('aset.scanner');
    }

    
    /**
     * Memproses hasil scan barcode.
     */
    public function scanProses(Request $request)
    {
        $request->validate([
            'nomor_aset' => 'required|string'
        ]);

        $inputData = trim($request->nomor_aset);

        // Cek hasil scan berupa URL
        if (filter_var($inputData, FILTER_VALIDATE_URL)) {
            // Ekstrak path dari URL
            $path = parse_url($inputData, PHP_URL_PATH);
            $segments = explode('/', trim($path, '/'));
            
            // Ambil ID aset
            $id = end($segments);

            $aset = DataAset::find($id);
            if ($aset) {
                return redirect()->route('aset.show', $aset->id)
                    ->with('success', 'Aset berhasil ditemukan dari QR code (URL).');
            }
        }

        //input manual nomor aset
        $aset = DataAset::where('nomor_aset', $inputData)->first();

        if ($aset) {
            return redirect()->route('aset.show', $aset->id)
                ->with('success', 'Aset berhasil ditemukan dari input manual.');
        }

        return redirect()->route('aset.scanner')
            ->with('error', 'Aset dengan identitas "' . $inputData . '" tidak ditemukan.');
    }

    /**
     * Menampilkan form untuk mengedit aset.
     */
    public function edit($id)
    {
        $aset = DataAset::with('foto')->findOrFail($id);

        $jenisKhusus       = JenisAsetKhusus::all();
        $lokasi            = LokasiAset::all();
        $sumberKepemilikan = SumberKepemilikan::all();
        $users             = User::all();

        $mainDirector = \App\Models\Director::with([
            'subDirectors',
            'divisi.department.section.unit'
        ])->whereNull('parent_director_id')->first();

        return view('aset.edit', compact(
            'aset', 'jenisKhusus', 'lokasi', 'sumberKepemilikan', 'users', 'mainDirector'
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
            'kode_organisasi'      => 'required|string',
            'lokasi_id'            => 'required|integer',
            'merek'                => 'nullable|string|max:100',
            'deskripsi'            => 'nullable|string',
            'tahun_kapitalisasi'   => 'nullable|integer|min:1900|max:' . date('Y'),
            'pic_id'               => 'nullable|integer',
            'status_kondisi'       => 'required|in:Baik,Rusak,Bongkar,Tidak Terpakai,Hilang,Tidak Teridentifikasi,Lainnya',
            'status_aset'          => 'required|in:Aktif,Tidak Aktif,Dalam Perbaikan,Dipinjam,Hilang',
            'keterangan_kondisi'   => 'nullable|string|max:255',
            'keterangan'           => 'nullable|string',
            // Tambah foto baru
            'foto_baru'            => 'nullable|array|max:10',
            'foto_baru.*'          => 'image|mimes:jpeg,png,jpg|max:4096',
            // ID foto yang mau dihapus
            'hapus_foto'           => 'nullable|array',
            'hapus_foto.*'         => 'integer|exists:aset_foto,id',
        ]);

        if (isset($validatedData['kode_organisasi'])) {
            $parts = explode('_', $validatedData['kode_organisasi']);
            if (count($parts) === 2) {
                $type = $parts[0];
                $id = $parts[1];
                
                // Reset semua org ID ke null
                $aset->id_director = null;
                $aset->id_divisi = null;
                $aset->id_department = null;
                $aset->id_section = null;
                $aset->id_unit = null;

                // Assign id baru yang benar
                $aset->{"id_{$type}"} = $id;
            }
            unset($validatedData['kode_organisasi']);
        }

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

    /**
     * Cetak Label Aset Tertentu (Multi-select)
     */
    public function cetakLabelSelected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:data_aset,id'
        ]);

        $asets = DataAset::with(['jenisAsetKhusus', 'lokasi'])->whereIn('id', $request->ids)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('aset.print_label_pdf', compact('asets'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream('Label_Aset_Selected.pdf');
    }

    /**
     * Preview Aset Per Lokasi untuk Modal
     */
    public function previewAsetLokasi($lokasi_id)
    {
        $asets = DataAset::with('jenisAsetKhusus')
            ->where('lokasi_id', $lokasi_id)
            ->get();
            
        return response()->json($asets);
    }

    /**
     * Cetak Label Aset Per Lokasi
     */
    public function cetakLabelPerLokasi(Request $request)
    {
        $request->validate([
            'lokasi_id' => 'required|exists:lokasi_aset,lokasi_id'
        ]);

        $asets = DataAset::with(['jenisAsetKhusus', 'lokasi'])
            ->where('lokasi_id', $request->lokasi_id)
            ->get();
            
        if ($asets->isEmpty()) {
            return back()->with('error', 'Tidak ada aset di ruangan ini.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('aset.print_label_pdf', compact('asets'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream("Label_Aset_Lokasi_{$request->lokasi_id}.pdf");
    }
}