<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BagianKerja;
use Illuminate\Validation\Rule;

class KodeBagianController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $filterKategori = $request->kategori;
        $filterStatus = $request->status;

        $query = BagianKerja::query()->withTrashed();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_bagian', 'LIKE', "%{$search}%")
                  ->orWhere('nama_bagian', 'LIKE', "%{$search}%");
            });
        }
        
        if ($filterKategori) {
            $query->where('kategori', $filterKategori);
        }

        if ($filterStatus === '1') {
            $query->whereNull('deleted_at')->where('is_active', true);
        }

        if ($filterStatus === '0') {
            $query->onlyTrashed();
        }

        $data = $query->orderBy('kode_bagian')->paginate($perPage)->withQueryString();

        return view('superadmin.kode_bagian.index', [
            'data' => $data,
            'filterKategori' => $filterKategori,
            'filterStatus' => $filterStatus,
            'kategoriList' => BagianKerja::getKategoriList(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_bagian' => 'required|string|max:20|unique:bagian_kerja,kode_bagian',
            'nama_bagian' => 'required|string|max:255',
            'kategori'    => 'nullable|string|max:100',
            'is_active'   => 'required|boolean',
        ], [
            'kode_bagian.required' => 'Kode Bagian wajib diisi.',
            'kode_bagian.unique'   => 'Kode Bagian ini sudah terdaftar.',
            'nama_bagian.required' => 'Nama Bagian wajib diisi.',
        ]);

        BagianKerja::create([
            'kode_bagian' => strtoupper($request->kode_bagian),
            'nama_bagian' => $request->nama_bagian,
            'kategori'    => $request->kategori,
            'is_active'   => $request->is_active,
        ]);

        return redirect()->route('kode-bagian.index')
            ->with('success', 'Kode bagian kerja berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $bagian = BagianKerja::findOrFail($id);

        $request->validate([
            'kode_bagian' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('bagian_kerja', 'kode_bagian')->ignore($bagian->id) 
            ],
            'nama_bagian' => 'required|string|max:255',
            'kategori'    => 'nullable|string|max:100',
            'is_active'   => 'required|boolean',
        ], [
            'kode_bagian.required' => 'Kode Bagian wajib diisi.',
            'kode_bagian.unique'   => 'Kode Bagian ini sudah terdaftar.',
            'nama_bagian.required' => 'Nama Bagian wajib diisi.',
        ]);

        $bagian->update([
            'kode_bagian' => strtoupper($request->kode_bagian),
            'nama_bagian' => $request->nama_bagian,
            'kategori'    => $request->kategori,
            'is_active'   => $request->is_active,
        ]);

        return redirect()->route('kode-bagian.index')
            ->with('success', 'Kode bagian kerja berhasil diperbarui');
    }

    public function destroy($id)
    {
        $bagian = BagianKerja::findOrFail($id);

        $bagian->update([
            'is_active' => false,
        ]);

        $bagian->delete(); 

        return redirect()->route('kode-bagian.index')
            ->with('success', 'Kode bagian kerja berhasil dihapus');
    }

    public function restore($id)
    {
        $bagian = BagianKerja::withTrashed()->findOrFail($id);

        $bagian->restore();

        $bagian->update([
            'is_active' => true,
        ]);

        return redirect()->route('kode-bagian.index')
            ->with('success', 'Kode bagian kerja berhasil dipulihkan');
    }
}