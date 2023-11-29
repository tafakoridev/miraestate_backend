<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function sendOtp($phonenumber, $code) {
        $api = new \Ghasedak\GhasedakApi( 'a5bc377f616c7e61023bff32c917ab9cfa9faec25773b1fb3948ed32bf9fd23d');
        return $api->Verify(
            $phonenumber,
            "miraestate",
            $code);
    }

    function generateRandomCode() {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function login(Request $request)
    {
        $code = $this->generateRandomCode();
        Cache::set($request->phonenumber, $code, 60);
        $this->sendOtp($request->phonenumber, $code);
        // Validation
        $validator = Validator::make($request->all(), [
            'phonenumber' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create user
        $user = User::firstOrCreate([
            'phonenumber' => $request->phonenumber,
        ]);

        return response()->json(['result' => "code sended to mobile", "retval" => true], 200);
    }

    public function checkCode(Request $request)
    {       
        $savedCode = Cache::get($request->phonenumber);

        // Validation
        $validator = Validator::make($request->all(), [
            'phonenumber' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where(['phonenumber' => $request->phonenumber])->first();

        // Attempt login
        if ($request->code === $savedCode && $user) {
            Auth::loginUsingId($user->id);
            $token = $request->user()->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token, "retval" => true], 200);
        } else {
            return response()->json(['result' => 'خطای اعتبار سنجی', "retval" => false], 401);
        }
    }
}