<?php

namespace App\Http\Controllers\Auth\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator, Hash, DB};

class RegisterController extends Controller
{
    use RegistersUsers;

    public function register(Request $request)
    {
        // リクエストを検証します。
        $this->validator($request->all())->validate();

        // ユーザーの作成とトークンの作成します
        $data = DB::transaction(function () use ($request) {
            $user = $this->create($request->all());
            // User::select(‘id’)->find(id);
            $user->only(['name', 'email']);
            // トークン名称は任意のものを指定
            // デバイス毎の値は取得できないです
            $token = $user->createToken('app')->plainTextToken;
            return json_encode(['token' => $token, 'user' => $user]);
        });

        return response($data);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
