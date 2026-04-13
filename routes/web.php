<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\KodeBagianController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManageController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SumberKepemilikanController;
use App\Http\Controllers\LokasiAsetController;
use App\Http\Controllers\JenisAsetController;
use App\Http\Controllers\DataAsetController;



// LOGIN
Route::get('/', function () {
    return view('auth.login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/logout', function () {
    return redirect()->route('login');
});

// SEMUA
Route::middleware(['auth', 'role:1,2,3'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ALIAS dashboard per role (buat tombol "kembali ke dashboard" sesuai role)
    Route::get('/dashboard/superadmin', [DashboardController::class, 'index'])
        ->middleware('role:1')
        ->name('superadmin.dashboard');

    Route::get('/dashboard/admin', [DashboardController::class, 'index'])
        ->middleware('role:2')
        ->name('admin.dashboard');

    Route::get('/dashboard/staff', [DashboardController::class, 'index'])
        ->middleware('role:3')
        ->name('staff.dashboard');
});

Route::get('/edit-profile', [ProfileController::class, 'editProfile'])->name('edit-profile');
Route::post('/delete-photo', [ProfileController::class, 'deletePhoto'])->name('superadmin.deletePhoto');
Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('superadmin.updateProfile');

// SUPERADMIN
Route::middleware(['auth', 'role:1'])->group(function () {

// Organization Controller
Route::put('/organization/{type}/{id}', [OrganizationController::class, 'update'])->name('organization.update');
Route::delete('/organization/{type}/{id}', [OrganizationController::class, 'delete'])->name('organization.delete');
Route::get('/organization-manage', [OrganizationController::class, 'index'])->name('organization.manageOrganization');
Route::post('organization-manage/add', [OrganizationController::class, 'store'])->name('organization-manage/add');

// Kode Bagian Controller
    Route::get('/kode-bagian', [KodeBagianController::class, 'index'])->name('kode-bagian.index');
    Route::get('/kode-bagian/create', [KodeBagianController::class, 'create'])->name('kode-bagian.create');
    Route::post('/kode-bagian', [KodeBagianController::class, 'store'])->name('kode-bagian.store');
    Route::get('/kode-bagian/{id}/edit', [KodeBagianController::class, 'edit'])->name('kode-bagian.edit');
    Route::put('/kode-bagian/{id}', [KodeBagianController::class, 'update'])->name('kode-bagian.update');
    Route::delete('/kode-bagian/{id}', [KodeBagianController::class, 'destroy'])->name('kode-bagian.destroy');
    Route::post('{id}/restore', [KodeBagianController::class, 'restore'])->name('kode-bagian.restore');
});

// manage user
    Route::get('/user-manage/edit/{id}', [UserController::class, 'edit'])->name('user-manage.edit');
    Route::delete('/user-manage/delete/{id}', [UserController::class, 'destroy'])->name('user-manage.destroy');
    Route::put('/user-manage/restore/{id}', [UserController::class, 'restore'])->name('user-manage.restore');
    Route::put('/user-manage/update/{id}', [UserController::class, 'update'])->name('user-manage/update');
    Route::get('/role-management', [UserController::class, 'showRole'])->name('user.role');
    Route::get('/user-manage/paginate', [UserManageController::class, 'paginateUsers'])->name('user-manage.paginate');
    Route::get('/user-manage', [UserManageController::class, 'index'])->name('user.manage');
    // Route::get('user-manage/add', [RegisteredUserController::class, 'create'])->name('user-manage/add');
    Route::post('user-manage/add', [RegisteredUserController::class, 'store'])->name('user-manage/add');
    Route::post('user-manage/import', [RegisteredUserController::class, 'import_ajax'])->name('user-manage.import');



Route::middleware(['auth', 'role:1,2'])->group(function () {

Route::resource('sumber-kepemilikan', SumberKepemilikanController::class)->middleware('auth');
Route::resource('lokasi-aset', LokasiAsetController::class)->middleware('auth');

// JENIS ASET UMUM
Route::get('/jenis-umum', [JenisAsetController::class, 'indexUmum'])->name('jenis-umum.index');
Route::post('/jenis-umum', [JenisAsetController::class, 'storeUmum'])->name('jenis-aset.storeUmum');
Route::put('/jenis-umum/{id}', [JenisAsetController::class, 'updateUmum'])->name('jenis-aset.updateUmum');
Route::delete('/jenis-umum/{id}', [JenisAsetController::class, 'destroyUmum'])->name('jenis-aset.destroyUmum');

// JENIS ASET KHUSUS
Route::get('/jenis-khusus', [JenisAsetController::class, 'indexKhusus'])->name('jenis-khusus.index');
Route::post('/jenis-khusus', [JenisAsetController::class, 'storeKhusus'])->name('jenis-aset.storeKhusus');
Route::put('/jenis-khusus/{id}', [JenisAsetController::class, 'updateKhusus'])->name('jenis-aset.updateKhusus');
Route::delete('/jenis-khusus/{id}', [JenisAsetController::class, 'destroyKhusus'])->name('jenis-aset.destroyKhusus');


Route::get('/aset', [DataAsetController::class, 'index'])->name('aset.index');
Route::get('/aset/create', [DataAsetController::class, 'create'])->name('aset.create');
Route::post('/aset', [DataAsetController::class, 'store'])->name('aset.store');
Route::get('/aset/{id}', [DataAsetController::class, 'show'])->name('aset.show');
Route::get('/aset/{id}/edit', [DataAsetController::class, 'edit'])->name('aset.edit');
Route::put('/aset/{id}', [DataAsetController::class, 'update'])->name('aset.update');
Route::delete('/aset/{id}', [DataAsetController::class, 'destroy'])->name('aset.destroy');

//Scanner QR Code
Route::get('/aset-scanner', [DataAsetController::class, 'scanner'])->name('aset.scanner');
Route::post('/aset-scanner/proses', [DataAsetController::class, 'scanProses'])->name('aset.scanProses');
});