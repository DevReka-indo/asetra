<?php

namespace App\Http\Controllers;

use App\Models\LokasiAset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LokasiAsetController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $data = LokasiAset::oldest()->paginate($perPage);
        
        return view('lokasi_aset.index', compact('data'));
    }

    public function create()
    {
        return view('lokasi_aset.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_lokasi' => 'required|string|max:20|unique:lokasi_aset,kode_lokasi',
            'nama_lokasi' => 'required|string|max:100|unique:lokasi_aset,nama_lokasi',
            'detail_lokasi' => 'nullable|string|max:255',
        ], [
            'kode_lokasi.required' => 'Kolom Kode Lokasi tidak boleh kosong.',
            'kode_lokasi.unique'   => 'Kode Lokasi ini sudah digunakan. Silakan masukkan kode lain.',
            'nama_lokasi.required' => 'Kolom Nama Lokasi tidak boleh kosong.',
            'nama_lokasi.unique'   => 'Nama Lokasi ini sudah terdaftar. Silakan masukkan nama lain.',
        ]);

        LokasiAset::create($request->only('kode_lokasi', 'nama_lokasi', 'detail_lokasi'));

        return redirect()->route('lokasi-aset.index')
            ->with('success', 'Data lokasi aset berhasil ditambahkan.');
    }

    public function show(LokasiAset $lokasiAset)
    {
        return view('lokasi_aset.show', compact('lokasiAset'));
    }

    public function edit(LokasiAset $lokasiAset)
    {
        return view('lokasi_aset.edit', compact('lokasiAset'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_lokasi' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('lokasi_aset', 'kode_lokasi')->ignore($id, 'lokasi_id')
            ],
            'nama_lokasi' => [
                'required', 
                'string', 
                'max:100',
                Rule::unique('lokasi_aset', 'nama_lokasi')->ignore($id, 'lokasi_id')
            ],
            'detail_lokasi' => 'nullable|string|max:255',
        ], [
            'kode_lokasi.required' => 'Kolom Kode Lokasi tidak boleh kosong.',
            'kode_lokasi.unique'   => 'Kode Lokasi ini sudah digunakan. Silakan masukkan kode lain.',
            'nama_lokasi.required' => 'Kolom Nama Lokasi tidak boleh kosong.',
            'nama_lokasi.unique'   => 'Nama Lokasi ini sudah terdaftar. Silakan masukkan nama lain.',
        ]);

        $lokasiAset = LokasiAset::findOrFail($id);
        $lokasiAset->update($request->only('kode_lokasi', 'nama_lokasi', 'detail_lokasi'));

        return redirect()->route('lokasi-aset.index')
            ->with('success', 'Data lokasi aset berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $lokasiAset = LokasiAset::findOrFail($id);
        $lokasiAset->delete();

        return redirect()->route('lokasi-aset.index')
            ->with('success', 'Data lokasi aset berhasil dihapus.');
    }
}