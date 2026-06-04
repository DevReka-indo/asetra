<?php

namespace App\Http\Controllers;

use App\Models\KategoriAset;
use App\Models\JenisKategori;
use App\Models\DataAset;
use App\Models\LokasiAset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PemulihanController extends Controller
{
    /**
     * Menampilkan daftar Kategori Aset yang dihapus.
     */
    public function kategoriAsetIndex(Request $request)
    {
        $perPage    = $request->input('per_page', 10);
        $search     = $request->input('search');
        $jenisId    = $request->input('jenis_kategori_id');
        $sortBy     = $request->input('sort_by', 'deleted_at');
        $orderBy    = $request->input('order_by', 'desc');

        $allowedSortColumns = ['nama', 'kode', 'jenis_kategori_id', 'deleted_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'deleted_at';
        }
        if (!in_array($orderBy, ['asc', 'desc'])) {
            $orderBy = 'desc';
        }

        $query = KategoriAset::onlyTrashed()->with('jenisKategori');

        if ($jenisId) {
            $query->where('jenis_kategori_id', $jenisId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $data       = $query->orderBy($sortBy, $orderBy)->paginate($perPage)->withQueryString();
        $jenisList  = JenisKategori::orderBy('kode_awalan')->get();
        $jenisAktif = $jenisId ? JenisKategori::find($jenisId) : null;
        
        return view('pemulihan.kategori_aset', compact('data', 'jenisList', 'jenisAktif'));
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

    /**
     * Menampilkan daftar Data Aset yang dihapus.
     */
    public function dataAsetIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = DataAset::onlyTrashed()->with('kategoriAset');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_aset', 'LIKE', "%{$search}%")
                  ->orWhere('nama_aset', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest('deleted_at')->paginate($perPage)->withQueryString();
        
        return view('pemulihan.data_aset', compact('data'));
    }

    /**
     * Memulihkan Data Aset.
     */
    public function dataAsetRestore($id)
    {
        $aset = DataAset::onlyTrashed()->findOrFail($id);
        $aset->restore();

        return redirect()->route('pemulihan.data-aset')
            ->with('success', 'Data aset berhasil dipulihkan.');
    }

    /**
     * Menghapus secara permanen Data Aset beserta Berita Acaranya.
     */
    public function dataAsetForceDelete($id)
    {
        $aset = DataAset::onlyTrashed()->findOrFail($id);
        
        // Hapus file dokumen penghapusan jika ada
        if ($aset->dokumen_penghapusan) {
            Storage::disk('public')->delete($aset->dokumen_penghapusan);
        }

        // Hapus semua foto dari storage
        foreach ($aset->foto as $foto) {
            Storage::disk('public')->delete($foto->path_foto);
            $foto->delete();
        }

        $aset->forceDelete();

        return redirect()->route('pemulihan.data-aset')
            ->with('success', 'Data aset berhasil dihapus secara permanen beserta dokumen terkait.');
    }

    /**
     * Menampilkan daftar Jenis Kategori yang dihapus.
     */
    public function jenisKategoriIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JenisKategori::onlyTrashed();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_jenis', 'LIKE', "%{$search}%")
                  ->orWhere('kode_awalan', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest('deleted_at')->paginate($perPage)->withQueryString();
        
        return view('pemulihan.jenis_kategori', compact('data'));
    }

    /**
     * Memulihkan Jenis Kategori.
     */
    public function jenisKategoriRestore($id)
    {
        $jenis = JenisKategori::onlyTrashed()->findOrFail($id);
        $jenis->restore();

        return redirect()->route('pemulihan.jenis-kategori')
            ->with('success', 'Jenis kategori berhasil dipulihkan.');
    }

    /**
     * Menghapus secara permanen Jenis Kategori.
     */
    public function jenisKategoriForceDelete($id)
    {
        $jenis = JenisKategori::onlyTrashed()->findOrFail($id);
        $jenis->forceDelete();

        return redirect()->route('pemulihan.jenis-kategori')
            ->with('success', 'Jenis kategori berhasil dihapus secara permanen.');
    }

    /**
     * Menampilkan daftar Lokasi Aset yang dihapus.
     */
    public function lokasiAsetIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = LokasiAset::onlyTrashed();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lokasi', 'LIKE', "%{$search}%")
                  ->orWhere('kode_lokasi', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest('deleted_at')->paginate($perPage)->withQueryString();
        
        return view('pemulihan.lokasi_aset', compact('data'));
    }

    /**
     * Memulihkan Lokasi Aset.
     */
    public function lokasiAsetRestore($id)
    {
        $lokasi = LokasiAset::onlyTrashed()->findOrFail($id);
        $lokasi->restore();

        return redirect()->route('pemulihan.lokasi-aset')
            ->with('success', 'Lokasi aset berhasil dipulihkan.');
    }

    /**
     * Menghapus secara permanen Lokasi Aset.
     */
    public function lokasiAsetForceDelete($id)
    {
        $lokasi = LokasiAset::onlyTrashed()->findOrFail($id);
        $lokasi->forceDelete();

        return redirect()->route('pemulihan.lokasi-aset')
            ->with('success', 'Lokasi aset berhasil dihapus secara permanen.');
    }
}
