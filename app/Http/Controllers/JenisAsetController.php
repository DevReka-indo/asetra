<?php

namespace App\Http\Controllers;

use App\Models\JenisAsetUmum;
use App\Models\JenisAsetKhusus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 

class JenisAsetController extends Controller
{
    public function indexUmum(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JenisAsetUmum::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_umum', 'LIKE', "%{$search}%")
                  ->orWhere('jenis_aset', 'LIKE', "%{$search}%");
            });
        }

        $dataUmum = $query->latest()->paginate($perPage)->withQueryString();
        
        return view('jenis_umum.index', compact('dataUmum'));
    }

    public function storeUmum(Request $request)
    {
        $request->validate([
            'kode_umum'  => 'required|string|max:10|unique:jenis_aset_umum,kode_umum',
            'jenis_aset' => 'required|string|max:100|unique:jenis_aset_umum,jenis_aset',
        ], [
            'kode_umum.required' => 'Kolom Kode Umum tidak boleh kosong.',
            'kode_umum.unique'   => 'Kode Umum tersebut sudah digunakan. Silahkan masukkan kode lain.',
            'kode_umum.max'      => 'Kode Umum maksimal 10 karakter.',
            'jenis_aset.required'=> 'Kolom Jenis Aset tidak boleh kosong.',
            'jenis_aset.unique'  => 'Nama Jenis Aset ini sudah ada. Silakan gunakan nama lain.',
        ]);

        JenisAsetUmum::create($request->only('kode_umum', 'jenis_aset'));

        return redirect()->route('jenis-umum.index')
            ->with('success', 'Jenis aset umum berhasil ditambahkan.');
    }

    public function updateUmum(Request $request, $id)
    {
        $request->validate([
            'kode_umum'  => [
                'required', 'string', 'max:10',
                Rule::unique('jenis_aset_umum', 'kode_umum')->ignore($id)
            ],
            'jenis_aset' => [
                'required', 'string', 'max:100',
                Rule::unique('jenis_aset_umum', 'jenis_aset')->ignore($id)
            ],
        ], [
            'kode_umum.required'  => 'Kolom Kode Umum tidak boleh kosong.',
            'kode_umum.unique'    => 'Kode Umum tersebut sudah digunakan. Silahkan masukkan kode lain.',
            'kode_umum.max'       => 'Kode Umum maksimal 10 karakter.',
            'jenis_aset.required' => 'Kolom Jenis Aset tidak boleh kosong.',
            'jenis_aset.unique'   => 'Nama Jenis Aset Umum ini sudah ada. Silakan gunakan nama lain.', 
        ]);

        $umum = JenisAsetUmum::findOrFail($id);
        $umum->update($request->only('kode_umum', 'jenis_aset'));

        return redirect()->route('jenis-umum.index')
            ->with('success', 'Jenis aset umum berhasil diperbarui.');
    }

    public function destroyUmum($id)
    {
        $asetUmum = JenisAsetUmum::findOrFail($id);
        $asetUmum->delete();

        return redirect()->route('jenis-umum.index')
            ->with('success', 'Jenis aset umum berhasil dipindahkan ke menu Pemulihan.');
    }

    public function indexKhusus(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $umumId  = $request->input('jenis_aset_umum_id');

        $query = JenisAsetKhusus::with('jenisAsetUmum');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_khusus', 'LIKE', "%{$search}%")
                  ->orWhere('jenis_aset', 'LIKE', "%{$search}%")
                  ->orWhereHas('jenisAsetUmum', function($qUmum) use ($search) {
                      $qUmum->where('kode_umum', 'LIKE', "%{$search}%")
                            ->orWhere('jenis_aset', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($umumId) {
            $query->where('jenis_aset_umum_id', $umumId);
        }

        $dataKhusus = $query->latest()->paginate($perPage)->withQueryString();

        $listUmum = JenisAsetUmum::select('id', 'kode_umum', 'jenis_aset')->get();

        return view('jenis_khusus.index', compact('dataKhusus', 'listUmum'));
    }

    public function storeKhusus(Request $request)
    {
        $request->validate([
            'jenis_aset_umum_id' => 'required|exists:jenis_aset_umum,id',
            'kode_khusus' => [
                'required', 'string', 'max:10',
                Rule::unique('jenis_aset_khusus', 'kode_khusus')->where(function ($query) use ($request) {
                    return $query->where('jenis_aset_umum_id', $request->jenis_aset_umum_id);
                })
            ],
            'jenis_aset' => [
                'required', 'string', 'max:100',
                Rule::unique('jenis_aset_khusus', 'jenis_aset')->where(function ($query) use ($request) {
                    return $query->where('jenis_aset_umum_id', $request->jenis_aset_umum_id);
                })
            ],
        ], [
            'jenis_aset_umum_id.required' => 'Silakan pilih Jenis Aset Umum terlebih dahulu.',
            'kode_khusus.required'        => 'Kolom Kode Khusus tidak boleh kosong.',
            'kode_khusus.unique'          => 'Kode Khusus ini sudah ada di Jenis Aset Umum tersebut!',
            'jenis_aset.required'         => 'Kolom Jenis Aset Khusus tidak boleh kosong.',
            'jenis_aset.unique'           => 'Nama aset ini sudah terdaftar di Jenis Aset Khusus tersebut!',
        ]);

        JenisAsetKhusus::create($request->only('jenis_aset_umum_id', 'kode_khusus', 'jenis_aset'));

        return redirect()->route('jenis-khusus.index')
            ->with('success', 'Jenis aset tetap khusus berhasil ditambahkan.');
    }

    public function updateKhusus(Request $request, $id)
    {
        $request->validate([
            'jenis_aset_umum_id' => 'required|exists:jenis_aset_umum,id',
            'kode_khusus' => [
                'required', 'string', 'max:10',
                Rule::unique('jenis_aset_khusus', 'kode_khusus')->where(function ($query) use ($request) {
                    return $query->where('jenis_aset_umum_id', $request->jenis_aset_umum_id);
                })->ignore($id)
            ],
            'jenis_aset' => [
                'required', 'string', 'max:100',
                Rule::unique('jenis_aset_khusus', 'jenis_aset')->where(function ($query) use ($request) {
                    return $query->where('jenis_aset_umum_id', $request->jenis_aset_umum_id);
                })->ignore($id)
            ],
        ], [
            'jenis_aset_umum_id.required' => 'Silakan pilih Jenis Aset Umum terlebih dahulu.',
            'jenis_aset_umum_id.exists'   => 'Pilihan Jenis Aset Umum tidak valid.',
            'kode_khusus.required'        => 'Kolom Kode Khusus tidak boleh kosong.',
            'kode_khusus.unique'          => 'Kode Khusus ini sudah ada di bawah Jenis Aset Umum tersebut!',
            'jenis_aset.required'         => 'Kolom Jenis Aset Khusus tidak boleh kosong.',
            'jenis_aset.unique'           => 'Nama aset ini sudah terdaftar di bawah Jenis Aset Umum tersebut!',
        ]);

        $khusus = JenisAsetKhusus::findOrFail($id);
        $khusus->update($request->only('jenis_aset_umum_id', 'kode_khusus', 'jenis_aset'));

        return redirect()->route('jenis-khusus.index')
            ->with('success', 'Jenis aset tetap khusus berhasil diperbarui.');
    }

    public function destroyKhusus($id)
    {
        $asetKhusus = JenisAsetKhusus::findOrFail($id);
        $asetKhusus->delete();

        return redirect()->route('jenis-khusus.index')
            ->with('success', 'Jenis aset khusus berhasil dipindahkan ke menu Pemulihan.');
    }
}