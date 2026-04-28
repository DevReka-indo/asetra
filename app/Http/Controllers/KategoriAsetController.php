<?php

namespace App\Http\Controllers;

use App\Models\KategoriAset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriAsetController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = KategoriAset::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama_kategori', 'LIKE', "%{$search}%");
            });
        }

        $dataKategori = $query->oldest()->paginate($perPage)->withQueryString();
        
        return view('kategori_aset.index', compact('dataKategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'  => 'required|string|max:10|unique:kategori_aset,kode',
            'nama_kategori' => 'required|string|max:100|unique:kategori_aset,nama_kategori',
        ], [
            'kode.required' => 'Kolom Kode Kategori Aset tidak boleh kosong.',
            'kode.unique'   => 'Kode Kategori Aset tersebut sudah digunakan. Silahkan masukkan kode lain.',
            'kode.max'      => 'Kode Kategori Aset maksimal 10 karakter.',
            'nama_kategori.required'=> 'Kolom Nama Kategori Aset tidak boleh kosong.',
            'nama_kategori.unique'  => 'Nama Kategori Aset ini sudah ada. Silakan gunakan nama lain.',
        ]);

        KategoriAset::create($request->only('kode', 'nama_kategori'));

        return redirect()->route('kategori-aset.index')
            ->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode'  => [
                'required', 'string', 'max:10',
                Rule::unique('kategori_aset', 'kode')->ignore($id, 'kategori_id')
            ],
            'nama_kategori' => [
                'required', 'string', 'max:100',
                Rule::unique('kategori_aset', 'nama_kategori')->ignore($id, 'kategori_id')
            ],
        ], [
            'kode.required'  => 'Kolom Kode Kategori Aset tidak boleh kosong.',
            'kode.unique'    => 'Kode Kategori Aset tersebut sudah digunakan. Silahkan masukkan kode lain.',
            'kode.max'       => 'Kode Kategori Aset maksimal 10 karakter.',
            'nama_kategori.required' => 'Kolom Nama Kategori Aset tidak boleh kosong.',
            'nama_kategori.unique'   => 'Nama Kategori Aset ini sudah ada. Silakan gunakan nama lain.', 
        ]);

        $kategori = KategoriAset::findOrFail($id);
        $kategori->update($request->only('kode', 'nama_kategori'));

        return redirect()->route('kategori-aset.index')
            ->with('success', 'Kategori aset berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = KategoriAset::findOrFail($id);
        $kategori->delete();

        return redirect()->route('kategori-aset.index')
            ->with('success', 'Kategori aset berhasil dipindahkan ke menu Pemulihan.');
    }
}
