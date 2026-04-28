<?php

namespace App\Http\Controllers;

use App\Models\JenisAsetUmum;
use App\Models\JenisAsetKhusus;
use App\Models\KategoriAset;
use Illuminate\Http\Request;

class PemulihanController extends Controller
{
    /**
     * Menampilkan daftar Jenis Aset Umum yang dihapus.
     */
    public function jenisUmumIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JenisAsetUmum::onlyTrashed();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_umum', 'LIKE', "%{$search}%")
                  ->orWhere('jenis_aset', 'LIKE', "%{$search}%");
            });
        }

        $dataUmum = $query->latest('deleted_at')->paginate($perPage)->withQueryString();
        
        return view('pemulihan.jenis_umum', compact('dataUmum'));
    }

    /**
     * Memulihkan Jenis Aset Umum.
     */
    public function jenisUmumRestore($id)
    {
        $asetUmum = JenisAsetUmum::onlyTrashed()->findOrFail($id);
        $asetUmum->restore();

        return redirect()->route('pemulihan.jenis-umum')
            ->with('success', 'Jenis aset umum berhasil dipulihkan.');
    }

    /**
     * Menghapus secara permanen Jenis Aset Umum.
     */
    public function jenisUmumForceDelete($id)
    {
        $asetUmum = JenisAsetUmum::onlyTrashed()->findOrFail($id);
        
        $asetUmum->forceDelete();

        return redirect()->route('pemulihan.jenis-umum')
            ->with('success', 'Jenis aset umum berhasil dihapus secara permanen.');
    }

    /**
     * Menampilkan daftar Jenis Aset Khusus yang dihapus.
     */
    public function jenisKhususIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JenisAsetKhusus::onlyTrashed();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_khusus', 'LIKE', "%{$search}%")
                  ->orWhere('jenis_aset', 'LIKE', "%{$search}%");
            });
        }

        $dataKhusus = $query->latest('deleted_at')->paginate($perPage)->withQueryString();
        
        return view('pemulihan.jenis_khusus', compact('dataKhusus'));
    }

    /**
     * Memulihkan Jenis Aset Khusus.
     */
    public function jenisKhususRestore($id)
    {
        $asetKhusus = JenisAsetKhusus::onlyTrashed()->findOrFail($id);
        $asetKhusus->restore();

        return redirect()->route('pemulihan.jenis-khusus')
            ->with('success', 'Jenis aset khusus berhasil dipulihkan.');
    }

    /**
     * Menghapus secara permanen Jenis Aset Khusus.
     */
    public function jenisKhususForceDelete($id)
    {
        $asetKhusus = JenisAsetKhusus::onlyTrashed()->findOrFail($id);
        
        $asetKhusus->forceDelete();

        return redirect()->route('pemulihan.jenis-khusus')
            ->with('success', 'Jenis aset khusus berhasil dihapus secara permanen.');
    }

    /**
     * Menampilkan daftar Kategori Aset yang dihapus.
     */
    public function kategoriAsetIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = KategoriAset::onlyTrashed();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama_kategori', 'LIKE', "%{$search}%");
            });
        }

        $dataKategori = $query->latest('deleted_at')->paginate($perPage)->withQueryString();
        
        return view('pemulihan.kategori_aset', compact('dataKategori'));
    }

    /**
     * Memulihkan Kategori Aset.
     */
    public function kategoriAsetRestore($id)
    {
        $kategori = KategoriAset::onlyTrashed()->findOrFail($id);
        $kategori->restore();

        return redirect()->route('pemulihan.kategori-aset')
            ->with('success', 'Kategori aset berhasil dipulihkan.');
    }

    /**
     * Menghapus secara permanen Kategori Aset.
     */
    public function kategoriAsetForceDelete($id)
    {
        $kategori = KategoriAset::onlyTrashed()->findOrFail($id);
        
        $kategori->forceDelete();

        return redirect()->route('pemulihan.kategori-aset')
            ->with('success', 'Kategori aset berhasil dihapus secara permanen.');
    }
}
