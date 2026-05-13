<?php

namespace App\Http\Controllers;

use App\Models\KategoriAset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KategoriAsetImport;
use App\Exports\TemplateExport;

class KategoriAsetController extends Controller
{
    /**
     * Menampilkan daftar Kategori Aset Tetap (1xx)
     */
    public function indexTetap(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = KategoriAset::asetTetap();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest()->paginate($perPage)->withQueryString();
        $title = "Kategori Aset Tetap";
        $type = "aset_tetap";
        
        return view('kategori_aset.index', compact('data', 'title', 'type'));
    }

    /**
     * Menampilkan daftar Kategori Aset EC (2xx)
     */
    public function indexInventaris(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = KategoriAset::inventaris();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest()->paginate($perPage)->withQueryString();
        $title = "Kategori Aset EC";
        $type = "inventaris";
        
        return view('kategori_aset.index', compact('data', 'title', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:10|unique:kategori_aset,kode',
            'nama' => 'required|string|max:100',
        ], [
            'kode.required' => 'Kolom Kode tidak boleh kosong.',
            'kode.unique'   => 'Kode tersebut sudah digunakan.',
            'nama.required' => 'Kolom Nama tidak boleh kosong.',
        ]);

        $kategori = KategoriAset::create($request->only('kode', 'nama'));

        $routeName = $kategori->tipe === 'aset_tetap' ? 'kategori-tetap.index' : 'kategori-inventaris.index';

        return redirect()->route($routeName)
            ->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode' => [
                'required', 'string', 'max:10',
                Rule::unique('kategori_aset', 'kode')->ignore($id)
            ],
            'nama' => 'required|string|max:100',
        ], [
            'kode.required' => 'Kolom Kode tidak boleh kosong.',
            'kode.unique'   => 'Kode tersebut sudah digunakan.',
            'nama.required' => 'Kolom Nama tidak boleh kosong.',
        ]);

        $kategori = KategoriAset::findOrFail($id);
        $kategori->update($request->only('kode', 'nama'));

        $routeName = $kategori->tipe === 'aset_tetap' ? 'kategori-tetap.index' : 'kategori-inventaris.index';

        return redirect()->route($routeName)
            ->with('success', 'Kategori aset berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = KategoriAset::findOrFail($id);
        $type = $kategori->tipe;
        $kategori->delete();

        $routeName = $type === 'aset_tetap' ? 'kategori-tetap.index' : 'kategori-inventaris.index';

        return redirect()->route($routeName)
            ->with('success', 'Kategori aset berhasil dipindahkan ke menu Pemulihan.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'type' => 'required|in:aset_tetap,inventaris'
        ]);

        try {
            Excel::import(new KategoriAsetImport, $request->file('file'));
            
            $routeName = $request->type === 'aset_tetap' ? 'kategori-tetap.index' : 'kategori-inventaris.index';
            
            return redirect()->route($routeName)->with('success', 'Data Kategori Aset berhasil diimport.');
        } catch (\Exception $e) {
            $routeName = $request->type === 'aset_tetap' ? 'kategori-tetap.index' : 'kategori-inventaris.index';
            return redirect()->route($routeName)->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new TemplateExport(['kode', 'nama']), 'template_kategori_aset.xlsx');
    }
}
