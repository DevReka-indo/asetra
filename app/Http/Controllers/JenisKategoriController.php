<?php

namespace App\Http\Controllers;

use App\Models\JenisKategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JenisKategoriImport;
use App\Exports\TemplateExport;
use App\Exports\JenisKategoriExport;

class JenisKategoriController extends Controller
{
    /**
     * Menampilkan daftar Jenis Kategori.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $sortBy  = $request->input('sort_by', 'nama_jenis');
        $orderBy = $request->input('order_by', 'asc');

        // Whitelist columns to prevent SQL injection
        $allowedSortColumns = ['nama_jenis', 'kode_awalan', 'kategori_aset_count'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'nama_jenis';
        }
        if (!in_array($orderBy, ['asc', 'desc'])) {
            $orderBy = 'asc';
        }

        $query = JenisKategori::withCount('kategoriAset');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_awalan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_jenis', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy($sortBy, $orderBy)->paginate($perPage)->withQueryString();

        return view('jenis_kategori.index', compact('data'));
    }

    /**
     * Simpan Jenis Kategori baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_awalan' => 'required|string|max:10|unique:jenis_kategori,kode_awalan',
            'nama_jenis'  => 'required|string|max:100',
            'warna_label' => 'required|string|max:7',
        ], [
            'kode_awalan.required' => 'Kode Awalan tidak boleh kosong.',
            'kode_awalan.max'      => 'Kode Awalan tidak boleh lebih dari 10 karakter.',
            'kode_awalan.unique'   => 'Kode Awalan tersebut sudah digunakan.',
            'nama_jenis.required'  => 'Nama Jenis tidak boleh kosong.',
            'nama_jenis.max'      => 'Nama Jenis tidak boleh lebih dari 100 karakter.',
            'warna_label.required' => 'Warna Label tidak boleh kosong.',
            'warna_label.max'      => 'Warna Label tidak boleh lebih dari 7 karakter.',
        ]);

        $data = $request->only('kode_awalan', 'nama_jenis', 'warna_label');
        if (empty($data['warna_label'])) {
            $data['warna_label'] = '#FF5E9B';
        }

        JenisKategori::create($data);

        return redirect()->route('jenis-kategori.index')
            ->with('success', 'Jenis Kategori berhasil ditambahkan.');
    }

    /**
     * Update Jenis Kategori.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_awalan' => [
                'required', 'string', 'max:10',
                Rule::unique('jenis_kategori', 'kode_awalan')->ignore($id),
            ],
            'nama_jenis'  => 'required|string|max:100',
            'warna_label' => 'required|string|max:7',
        ], [
            'kode_awalan.required' => 'Kode Awalan tidak boleh kosong.',
            'kode_awalan.max'      => 'Kode Awalan tidak boleh lebih dari 10 karakter.',
            'kode_awalan.unique'   => 'Kode Awalan tersebut sudah digunakan.',
            'nama_jenis.required'  => 'Nama Jenis tidak boleh kosong.',
            'nama_jenis.max'      => 'Nama Jenis tidak boleh lebih dari 100 karakter.',
            'warna_label.required' => 'Warna Label tidak boleh kosong.',
            'warna_label.max'      => 'Warna Label tidak boleh lebih dari 7 karakter.',
        ]);

        $jenis = JenisKategori::findOrFail($id);
        
        $data = $request->only('kode_awalan', 'nama_jenis', 'warna_label');
        if (empty($data['warna_label'])) {
            $data['warna_label'] = '#FF5E9B';
        }

        $jenis->update($data);

        return redirect()->route('jenis-kategori.index')
            ->with('success', 'Jenis Kategori berhasil diperbarui.');
    }

    /**
     * Hapus Jenis Kategori (soft delete).
     */
    public function destroy($id)
    {
        $jenis = JenisKategori::withCount('kategoriAset')->findOrFail($id);

        if ($jenis->kategori_aset_count > 0) {
            return redirect()->route('jenis-kategori.index')
                ->with('error', "Tidak dapat menghapus \"{$jenis->nama_jenis}\" karena masih memiliki {$jenis->kategori_aset_count} kategori aset aktif.");
        }

        $jenis->delete();

        return redirect()->route('jenis-kategori.index')
            ->with('success', 'Jenis Kategori berhasil dihapus.');
    }

    /**
     * Import Jenis Kategori dari file Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new JenisKategoriImport, $request->file('file'));

            return redirect()->route('jenis-kategori.index')
                ->with('success', 'Data Jenis Kategori berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('jenis-kategori.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel.
     */
    public function downloadTemplate()
    {
        return Excel::download(new TemplateExport(['nama_jenis', 'kode_awalan', 'warna_label']), 'template_jenis_kategori.xlsx');
    }

    /**
     * Export data Jenis Kategori ke Excel dengan pencarian aktif.
     */
    public function export(Request $request)
    {
        $search = $request->input('search');
        return Excel::download(new JenisKategoriExport($search), 'jenis_kategori_export.xlsx');
    }
}
