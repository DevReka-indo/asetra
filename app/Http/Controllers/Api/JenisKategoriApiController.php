<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisKategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisKategoriApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page');

        $query = JenisKategori::withCount('kategoriAset');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_awalan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_jenis', 'LIKE', "%{$search}%");
            });
        }

        if ($perPage) {
            $data = $query->latest()->paginate($perPage);
        } else {
            $data = $query->latest()->get();
        }

        return $this->success($data, 'Data jenis kategori retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_awalan' => 'required|string|max:10|unique:jenis_kategori,kode_awalan',
            'nama_jenis'  => 'required|string|max:100',
            'warna_label' => 'nullable|string|max:7',
        ]);

        $data = $request->only('kode_awalan', 'nama_jenis', 'warna_label');
        if (empty($data['warna_label'])) {
            $data['warna_label'] = '#FF5E9B';
        }

        $jenis = JenisKategori::create($data);

        return $this->success($jenis, 'Jenis kategori berhasil ditambahkan.', 210);
    }

    public function show($id)
    {
        $jenis = JenisKategori::with('kategoriAset')->findOrFail($id);
        return $this->success($jenis, 'Data jenis kategori retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $jenis = JenisKategori::findOrFail($id);

        $request->validate([
            'kode_awalan' => [
                'required', 'string', 'max:10',
                Rule::unique('jenis_kategori', 'kode_awalan')->ignore($id),
            ],
            'nama_jenis'  => 'required|string|max:100',
            'warna_label' => 'nullable|string|max:7',
        ]);

        $data = $request->only('kode_awalan', 'nama_jenis', 'warna_label');
        if (empty($data['warna_label'])) {
            $data['warna_label'] = '#FF5E9B';
        }

        $jenis->update($data);

        return $this->success($jenis, 'Jenis kategori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jenis = JenisKategori::withCount('kategoriAset')->findOrFail($id);

        if ($jenis->kategori_aset_count > 0) {
            return $this->error("Tidak dapat menghapus \"{$jenis->nama_jenis}\" karena masih memiliki {$jenis->kategori_aset_count} kategori aset aktif.", 422);
        }

        $jenis->delete();

        return $this->success(null, 'Jenis kategori berhasil dihapus.');
    }
}
