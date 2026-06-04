<?php

namespace App\Http\Controllers\Api;

use App\Models\KategoriAset;
use App\Models\JenisKategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriAsetApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page');
        $search = $request->input('search');
        $jenisId = $request->input('jenis_kategori_id');

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

        if ($perPage) {
            $data = $query->oldest()->paginate($perPage);
        } else {
            $data = $query->oldest()->get();
        }

        return $this->success($data, 'Data kategori aset retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'              => 'required|string|max:10|unique:kategori_aset,kode',
            'nama'              => 'required|string|max:100',
            'jenis_kategori_id' => 'required|exists:jenis_kategori,id',
        ]);

        // Validasi prefix kode sesuai kode_awalan jenis kategori
        $jenis = JenisKategori::findOrFail($request->jenis_kategori_id);
        if (!str_starts_with((string) $request->kode, $jenis->kode_awalan)) {
            return $this->error("Kode kategori untuk \"{$jenis->nama_jenis}\" harus diawali dengan angka {$jenis->kode_awalan}.", 422, [
                'kode' => ["Kode kategori harus diawali dengan {$jenis->kode_awalan}."]
            ]);
        }

        $kategori = KategoriAset::create($request->only('kode', 'nama', 'jenis_kategori_id'));

        return $this->success($kategori, 'Kategori aset berhasil ditambahkan.', 210);
    }

    public function show($id)
    {
        $kategori = KategoriAset::with('jenisKategori')->findOrFail($id);
        return $this->success($kategori, 'Data kategori aset retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriAset::findOrFail($id);

        $request->validate([
            'kode' => [
                'required', 'string', 'max:10',
                Rule::unique('kategori_aset', 'kode')->ignore($id),
            ],
            'nama'              => 'required|string|max:100',
            'jenis_kategori_id' => 'required|exists:jenis_kategori,id',
        ]);

        // Validasi prefix kode
        $jenis = JenisKategori::findOrFail($request->jenis_kategori_id);
        if (!str_starts_with((string) $request->kode, $jenis->kode_awalan)) {
            return $this->error("Kode kategori untuk \"{$jenis->nama_jenis}\" harus diawali dengan angka {$jenis->kode_awalan}.", 422, [
                'kode' => ["Kode kategori harus diawali dengan {$jenis->kode_awalan}."]
            ]);
        }

        $kategori->update($request->only('kode', 'nama', 'jenis_kategori_id'));

        return $this->success($kategori, 'Kategori aset berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = KategoriAset::findOrFail($id);
        $kategori->delete();

        return $this->success(null, 'Kategori aset berhasil dihapus.');
    }
}
