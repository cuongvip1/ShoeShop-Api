<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Lấy danh sách tất cả người dùng.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(User::all());
    }

    /**
     * Lấy thông tin chi tiết một người dùng theo ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $userArray = $user->toArray();
        unset($userArray['password']); // Không trả về mật khẩu
        return response()->json($userArray);
    }

    /**
     * Tạo người dùng mới.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->all();

        // Validate required fields and uniqueness
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

        // Hash mật khẩu trước khi lưu
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = User::create($data);
        $userArray = $user->toArray();
        unset($userArray['password']); // Không trả về mật khẩu
        return response()->json(['user' => $userArray], 201);
    }

    /**
     * Xử lý đăng nhập đơn giản.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $login = $request->input('ten_dang_nhap');
        $password = $request->input('password');

        $user = User::where('email', $login)->orWhere('Ten_dang_nhap', $login)->first();
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (Hash::check($password, $user->password)) {
            $userArray = $user->toArray();
            unset($userArray['password']); // Không trả về mật khẩu
            return response()->json(['user' => $userArray]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
}