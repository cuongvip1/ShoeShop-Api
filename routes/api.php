<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController; 
use App\Http\Controllers\Api\ProductController;

use App\Models\LoaiGiay;
use App\Models\ThuongHieu;
use App\Models\KhuyenMai;
use App\Models\DanhGia;
use App\Models\PhanQuyen;
use App\Models\DonHang;
use App\Models\User;
use App\Models\Giay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Đây là nơi đăng ký các route API. Các route này là stateless và KHÔNG có Session.
|
*/

// --- USER ROUTES (SỬ DỤNG CONTROLLER) ---

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User CRUD operations using the dedicated UserController
Route::get('/users', [UserController::class, 'index']); // Thay thế closure: Route::get('/users', function()...
Route::get('/users/{id}', [UserController::class, 'show']); // Thay thế closure: Route::get('/users/{id}', function()...
Route::post('/users', [UserController::class, 'store']); // Thay thế closure: Route::post('/users', function()...
// Nếu cần cập nhật/xóa, bạn nên thêm các route sau vào đây (dựa trên cấu trúc cũ của bạn):
// Route::put('/users/{id}', [UserController::class, 'update']);
// Route::delete('/users/{id}', [UserController::class, 'destroy']); 

// Auth: simple login endpoint (returns user on success)
Route::post('/auth/login', [UserController::class, 'login']); // Thay thế closure: Route::post('/auth/login', function()...

// --- PRODUCT ROUTES ---

// Public product endpoints
Route::get('/giay', [ProductController::class, 'index']);
Route::get('/giay/{id}', [ProductController::class, 'show']);

// Product helpers (Giữ nguyên closure để tránh tạo quá nhiều Controller nhỏ nếu bạn muốn)
Route::get('/giay/noi-bat', function() {
    // Return top sold products (by so_luong_mua) limited to 8 items
    return \App\Models\Giay::orderBy('so_luong_mua', 'desc')->take(8)->get();
});

Route::get('/giay/moi-nhat', function() {
    // Return newest products by update time, limited to 8 items
    return \App\Models\Giay::orderBy('updated_at', 'desc')->take(8)->get();
});

Route::get('/giay/thuong-hieu/{thuonghieu}', function($thuonghieu) {
    return \App\Models\Giay::where('ten_thuong_hieu', $thuonghieu)->get();
});

Route::get('/giay/dang-giam-gia', function() {
    return \App\Models\Giay::where('ten_khuyen_mai', '!=', 'Không khuyến mãi')->get();
});

Route::get('/giay/{id}/update/{num}', function($id, $num) {
    return Giay::where('id_giay', $id)->update(['so_luot_xem' => $num]);
});

// --- OTHER RESOURCES ---

Route::get('/loai-giay', function() {
    return LoaiGiay::all();
});

Route::get('/thuong-hieu', function() {
    return ThuongHieu::all();
});

Route::get('/khuyen-mai', function() {
    return KhuyenMai::all();
});

Route::get('/danh-gia', function() {
    return DanhGia::all();
});

Route::get('/danh-gia/{id}', function($id) {
    return DanhGia::where('id_giay', $id)->get();
});

// Create review
use Illuminate\Http\Request as HttpRequest;
Route::post('/danh-gia', function(HttpRequest $request) {
    return DanhGia::create($request->all());
});

// Update review
Route::put('/danh-gia/{id}', function(HttpRequest $request, $id) {
    $dg = DanhGia::findOrFail($id);
    $dg->update($request->all());
    return $dg;
});

Route::get('/phan-quyen', function() {
    return PhanQuyen::all();
});

Route::get('/don-hang', function() {
    return DonHang::all();
});