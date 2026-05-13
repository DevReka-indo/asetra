<?php

namespace App\Http\Controllers;

use App\Models\KategoriAset;
use App\Models\DataAset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PemulihanController extends Controller
{
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
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest('deleted_at')->paginate($perPage)->withQueryString();
        
        return view('pemulihan.kategori_aset', compact('data'));
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
}
