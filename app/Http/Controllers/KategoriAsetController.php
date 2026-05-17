<?php

namespace App\Http\Controllers;

use App\Models\KategoriAset;
use App\Models\JenisKategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KategoriAsetImport;
use App\Exports\TemplateExport;
use App\Exports\KategoriAsetExport;

class KategoriAsetController extends Controller
{
    /**
     * Menampilkan semua Kategori Aset (terpadu), dengan filter opsional per jenis.
     */
    public function index(Request $request)
    {
        $perPage    = $request->input('per_page', 10);
        $search     = $request->input('search');
        $jenisId    = $request->input('jenis_kategori_id');

        $query = KategoriAset::with('jenisKategori');

        if ($jenisId) {
            $query->where('jenis_kategori_id', $jenisId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $data       = $query->oldest()->paginate($perPage)->withQueryString();
        $jenisList  = JenisKategori::orderBy('kode_awalan')->get();
        $jenisAktif = $jenisId ? JenisKategori::find($jenisId) : null;

        return view('kategori_aset.index', compact('data', 'jenisList', 'jenisAktif'));
    }

    /**
     * Simpan Kategori Aset baru dengan validasi prefix dinamis.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode'              => 'required|string|max:10|unique:kategori_aset,kode',
            'nama'              => 'required|string|max:100',
            'jenis_kategori_id' => 'required|exists:jenis_kategori,id',
        ], [
            'kode.required'              => 'Kolom Kode tidak boleh kosong.',
            'kode.unique'                => 'Kode tersebut sudah digunakan.',
            'nama.required'              => 'Kolom Nama tidak boleh kosong.',
            'jenis_kategori_id.required' => 'Jenis Kategori harus dipilih.',
            'jenis_kategori_id.exists'   => 'Jenis Kategori tidak valid.',
        ]);

        // Validasi prefix kode sesuai kode_awalan jenis kategori
        $jenis = JenisKategori::findOrFail($request->jenis_kategori_id);
        if (!str_starts_with((string) $request->kode, $jenis->kode_awalan)) {
            return back()->withInput()->withErrors([
                'kode' => "Kode kategori untuk \"{$jenis->nama_jenis}\" harus diawali dengan angka {$jenis->kode_awalan}.",
            ])->with('form_type', 'tambah');
        }

        KategoriAset::create($request->only('kode', 'nama', 'jenis_kategori_id'));

        return redirect()->route('kategori-aset.index')
            ->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    /**
     * Update Kategori Aset dengan validasi prefix dinamis.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kode' => [
                'required', 'string', 'max:10',
                Rule::unique('kategori_aset', 'kode')->ignore($id),
            ],
            'nama'              => 'required|string|max:100',
            'jenis_kategori_id' => 'required|exists:jenis_kategori,id',
        ], [
            'kode.required'              => 'Kolom Kode tidak boleh kosong.',
            'kode.unique'                => 'Kode tersebut sudah digunakan.',
            'nama.required'              => 'Kolom Nama tidak boleh kosong.',
            'jenis_kategori_id.required' => 'Jenis Kategori harus dipilih.',
        ]);

        // Validasi prefix kode
        $jenis = JenisKategori::findOrFail($request->jenis_kategori_id);
        if (!str_starts_with((string) $request->kode, $jenis->kode_awalan)) {
            return back()->withInput()->withErrors([
                'kode' => "Kode kategori untuk \"{$jenis->nama_jenis}\" harus diawali dengan angka {$jenis->kode_awalan}.",
            ])->with('form_type_edit', $id);
        }

        $kategori = KategoriAset::findOrFail($id);
        $kategori->update($request->only('kode', 'nama', 'jenis_kategori_id'));

        return redirect()->route('kategori-aset.index')
            ->with('success', 'Kategori aset berhasil diperbarui.');
    }

    /**
     * Hapus (soft delete) Kategori Aset.
     */
    public function destroy($id)
    {
        $kategori = KategoriAset::findOrFail($id);
        $kategori->delete();

        return redirect()->route('kategori-aset.index')
            ->with('success', 'Kategori aset berhasil dipindahkan ke menu Pemulihan.');
    }

    /**
     * Import Kategori Aset dari file Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'              => 'required|mimes:xlsx,xls,csv|max:2048',
            'jenis_kategori_id' => 'required|exists:jenis_kategori,id',
        ]);

        try {
            Excel::import(new KategoriAsetImport($request->jenis_kategori_id), $request->file('file'));

            return redirect()->route('kategori-aset.index')
                ->with('success', 'Data Kategori Aset berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('kategori-aset.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel.
     */
    public function downloadTemplate()
    {
        return Excel::download(new TemplateExport(['kode', 'nama']), 'template_kategori_aset.xlsx');
    }

    /**
     * Export data Kategori Aset ke Excel dengan pencarian dan filter jenis aktif.
     */
    public function export(Request $request)
    {
        $jenisKategoriId = $request->input('jenis_kategori_id');
        $search          = $request->input('search');

        return Excel::download(new KategoriAsetExport($jenisKategoriId, $search), 'kategori_aset_export.xlsx');
    }
}
