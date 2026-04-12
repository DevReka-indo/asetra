<?php

namespace App\Http\Controllers;

use App\Models\SumberKepemilikan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SumberKepemilikanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $data    = SumberKepemilikan::oldest()->paginate($perPage);

        return view('sumber_kepemilikan.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:20|unique:sumber_kepemilikan,kode',
            'nama' => 'required|string|max:100|unique:sumber_kepemilikan,nama',
        ], [
            'kode.required' => 'Kolom Kode Sumber tidak boleh kosong.',
            'kode.unique'   => 'Kode ini sudah digunakan. Silahkan masukkan kode lain.',
            'nama.required' => 'Kolom Nama Sumber tidak boleh kosong.',
            'nama.unique'   => 'Nama Sumber ini sudah digunakan. Silahkan masukkan nama lain.',
        ]);

        SumberKepemilikan::create($request->only('kode', 'nama'));

        return redirect()->route('sumber-kepemilikan.index')
            ->with('success', 'Data sumber kepemilikan berhasil ditambahkan.');
    }

    public function update(Request $request, SumberKepemilikan $sumberKepemilikan)
    {
        $request->validate([
            'kode' => [
                'required', 'string', 'max:20',
                Rule::unique('sumber_kepemilikan', 'kode')->ignore($sumberKepemilikan->id)
            ],
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('sumber_kepemilikan', 'nama')->ignore($sumberKepemilikan->id)
            ],
        ], [
            'kode.required' => 'Kolom Kode Sumber tidak boleh kosong.',
            'kode.unique'   => 'Kode ini sudah digunakan. Silakan masukkan kode lain.',
            'nama.required' => 'Kolom Nama Sumber tidak boleh kosong.',
            'nama.unique'   => 'Nama Sumber ini sudah terdaftar. Silakan masukkan nama lain.',
        ]);

        $sumberKepemilikan->update($request->only('kode', 'nama'));

        return redirect()->route('sumber-kepemilikan.index')
            ->with('success', 'Data sumber kepemilikan berhasil diperbarui.');
    }

    public function destroy(SumberKepemilikan $sumberKepemilikan)
    {
        $sumberKepemilikan->delete();

        return redirect()->route('sumber-kepemilikan.index')
            ->with('success', 'Data sumber kepemilikan berhasil dihapus.');
    }
}
