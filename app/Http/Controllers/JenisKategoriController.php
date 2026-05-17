<?php

namespace App\Http\Controllers;

use App\Models\JenisKategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisKategoriController extends Controller
{
    /**
     * Menampilkan daftar Jenis Kategori.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JenisKategori::withCount('kategoriAset');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_awalan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_jenis', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest()->paginate($perPage)->withQueryString();

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
            'warna_label' => 'nullable|string|max:7',
        ], [
            'kode_awalan.required' => 'Kode Awalan tidak boleh kosong.',
            'kode_awalan.unique'   => 'Kode Awalan tersebut sudah digunakan.',
            'nama_jenis.required'  => 'Nama Jenis tidak boleh kosong.',
        ]);

        $data = $request->only('kode_awalan', 'nama_jenis', 'warna_label');
        if (empty($data['warna_label'])) {
            $data['warna_label'] = '#ea6565';
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
            'warna_label' => 'nullable|string|max:7',
        ], [
            'kode_awalan.required' => 'Kode Awalan tidak boleh kosong.',
            'kode_awalan.unique'   => 'Kode Awalan tersebut sudah digunakan.',
            'nama_jenis.required'  => 'Nama Jenis tidak boleh kosong.',
        ]);

        $jenis = JenisKategori::findOrFail($id);
        
        $data = $request->only('kode_awalan', 'nama_jenis', 'warna_label');
        if (empty($data['warna_label'])) {
            $data['warna_label'] = '#ea6565';
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
}
