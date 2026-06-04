<?php

namespace App\Http\Controllers\Api;

use App\Models\LokasiAset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LokasiAsetApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page');

        $query = LokasiAset::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_lokasi', 'LIKE', "%{$search}%")
                  ->orWhere('nama_lokasi', 'LIKE', "%{$search}%")
                  ->orWhere('detail_lokasi', 'LIKE', "%{$search}%");
            });
        }

        if ($perPage) {
            $data = $query->oldest()->paginate($perPage);
        } else {
            $data = $query->oldest()->get();
        }

        return $this->success($data, 'Data lokasi aset retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_lokasi'   => 'required|string|max:45|unique:lokasi_aset,kode_lokasi',
            'nama_lokasi'   => 'required|string|max:100|unique:lokasi_aset,nama_lokasi',
            'detail_lokasi' => 'nullable|string|max:255',
        ]);

        $lokasi = LokasiAset::create($request->only('kode_lokasi', 'nama_lokasi', 'detail_lokasi'));

        return $this->success($lokasi, 'Data lokasi aset berhasil ditambahkan.', 210);
    }

    public function show($id)
    {
        $lokasi = LokasiAset::findOrFail($id);
        return $this->success($lokasi, 'Data lokasi aset retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $lokasi = LokasiAset::findOrFail($id);

        $request->validate([
            'kode_lokasi' => [
                'required', 
                'string', 
                'max:45',
                Rule::unique('lokasi_aset', 'kode_lokasi')->ignore($id, 'lokasi_id')
            ],
            'nama_lokasi' => [
                'required', 
                'string', 
                'max:100',
                Rule::unique('lokasi_aset', 'nama_lokasi')->ignore($id, 'lokasi_id')
            ],
            'detail_lokasi' => 'nullable|string|max:255',
        ]);

        $lokasi->update($request->only('kode_lokasi', 'nama_lokasi', 'detail_lokasi'));

        return $this->success($lokasi, 'Data lokasi aset berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $lokasi = LokasiAset::findOrFail($id);
        $lokasi->delete();

        return $this->success(null, 'Data lokasi aset berhasil dihapus.');
    }
}
