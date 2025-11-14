<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public product endpoints
use App\Http\Controllers\Api\ProductController;

Route::get('/giay', [ProductController::class, 'index']);
Route::get('/giay/{id}', [ProductController::class, 'show']);

// Other resources: categories, brands, promos, reviews, users, orders
use App\Models\LoaiGiay;
use App\Models\ThuongHieu;
use App\Models\KhuyenMai;
use App\Models\DanhGia;
use App\Models\PhanQuyen;
use App\Models\DonHang;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

// Users
Route::get('/users', function() {
    return User::all();
});

Route::get('/users/{id}', function($id) {
    return User::findOrFail($id);
});

Route::post('/users', function(Request $request) {
    $data = $request->all();

    // Validate required fields and uniqueness to avoid DB errors
    $validator = Validator::make($data, [
        'ten_nguoi_dung' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'Ten_dang_nhap' => 'required|string|unique:users,Ten_dang_nhap',
        'password' => 'required|string|min:5',
    ], [
        'email.unique' => 'Email đã được sử dụng.',
        'Ten_dang_nhap.unique' => 'Tên đăng nhập đã được sử dụng.',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
    }

    $user = User::create($data);
    $userArray = $user->toArray();
    unset($userArray['password']);
    return response()->json(['user' => $userArray], 201);
});

// Auth: simple login endpoint (returns user on success)
Route::post('/auth/login', function(Request $request) {
    $login = $request->input('ten_dang_nhap');
    $password = $request->input('password');

    $user = User::where('email', $login)->orWhere('Ten_dang_nhap', $login)->first();
    if (! $user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    if (Hash::check($password, $user->password)) {
        $userArray = $user->toArray();
        unset($userArray['password']);
        return response()->json(['user' => $userArray]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
});

// Product helpers
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
    return \App\Models\Giay::where('id_giay', $id)->update(['so_luot_xem' => $num]);
});
