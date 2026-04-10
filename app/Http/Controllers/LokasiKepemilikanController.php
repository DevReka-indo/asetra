<?php

namespace App\Http\Controllers;

use App\Models\LokasiKepemilikan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LokasiKepemilikanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $data = LokasiKepemilikan::oldest()->paginate($perPage);
        
        return view('lokasi_kepemilikan.index', compact('data'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'kode_lokasi_kepemilikan' => 'required|string|max:20|unique:lokasi_kepemilikan,kode_lokasi_kepemilikan',
            'nama_lokasi_kepemilikan' => 'required|string|max:100|unique:lokasi_kepemilikan,nama_lokasi_kepemilikan',
        ], [
            'kode_lokasi_kepemilikan.required' => 'Kolom Kode Lokasi tidak boleh kosong.',
            'kode_lokasi_kepemilikan.unique'   => 'Kode Lokasi ini sudah digunakan. Silahkan masukkan kode lain.',
            'nama_lokasi_kepemilikan.required' => 'Kolom Nama Lokasi tidak boleh kosong.',
            'nama_lokasi_kepemilikan.unique'   => 'Nama Lokasi ini sudah digunakan. Silahkan masukkan nama lain.',
        ]);

        LokasiKepemilikan::create($request->only('kode_lokasi_kepemilikan', 'nama_lokasi_kepemilikan'));

        return redirect()->route('lokasi-kepemilikan.index')
            ->with('success', 'Data lokasi kepemilikan berhasil ditambahkan.');
    }

    public function update(Request $request, LokasiKepemilikan $lokasiKepemilikan)
    {
        $request->validate([
            'kode_lokasi_kepemilikan' => [
                'required', 'string', 'max:20',
                Rule::unique('lokasi_kepemilikan', 'kode_lokasi_kepemilikan')->ignore($lokasiKepemilikan->lokasi_kepemilikan_id, 'lokasi_kepemilikan_id')
            ],
            'nama_lokasi_kepemilikan' => [
                'required', 'string', 'max:100',
                Rule::unique('lokasi_kepemilikan', 'nama_lokasi_kepemilikan')->ignore($lokasiKepemilikan->lokasi_kepemilikan_id, 'lokasi_kepemilikan_id')
            ],
        ], [
            'kode_lokasi_kepemilikan.required' => 'Kolom Kode Lokasi tidak boleh kosong.',
            'kode_lokasi_kepemilikan.unique'   => 'Kode Lokasi ini sudah digunakan. Silakan masukkan kode lain.',
            'nama_lokasi_kepemilikan.required' => 'Kolom Nama Lokasi tidak boleh kosong.',
            'nama_lokasi_kepemilikan.unique'   => 'Nama Lokasi ini sudah terdaftar. Silakan masukkan nama lain.',
        ]);

        $lokasiKepemilikan->update($request->only('kode_lokasi_kepemilikan', 'nama_lokasi_kepemilikan'));

        return redirect()->route('lokasi-kepemilikan.index')
            ->with('success', 'Data lokasi kepemilikan berhasil diperbarui.');
    }

    public function destroy(LokasiKepemilikan $lokasiKepemilikan)
    {
        $lokasiKepemilikan->delete();

        return redirect()->route('lokasi-kepemilikan.index')
            ->with('success', 'Data lokasi kepemilikan berhasil dihapus.');
    }
}