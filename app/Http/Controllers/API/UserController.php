<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password'), 'status' => 0])) {
            return response()->json(['error' => true, 'message' => 'Sorry not active account ', 'data' => []], 401);
        } elseif (Auth::attempt(['email' => request('email'), 'password' => request('password'), 'status' => 1])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('Alsson')->accessToken;
            $user->api_token = $success['token'];
            $user->save();
            return response()->json(['error' => false, 'message' => 'success','token' => $user->api_token, 'data' => $user], 200);
        } else {
            return response()->json(['error' => true, 'message' => 'Unauthorised', 'data' => []], 401);
        }
    }

    public function details(Request $request)
    {
        $user = User::with('roles')->where('api_token', $request->bearerToken())->first();
        if (isset($user->status) && $user->status == 1) {
            $permissionsofRole=$user->roles[0]->getAllPermissions()->pluck('name');
            $permissionsofUser=$user->getAllPermissions()->pluck('name');
            //dd($permissionsofUser);
            return response()->json([
                'id' => $user->id,
                'api_token' => $user->api_token,
                'ask_change_pass' => $user->ask_change_pass,
                'roles' => $user->roles->toArray(),
                'school' => $user->school ?? explode(',', $user->school),
                'permissions' => $permissionsofUser,
                'name' => $user->name,
                'avatar' => $user->image ?? 'uploads/default.png',
//                'introduction' => $user->title,
            ]);
        }    //return response()->json(['user' => $user], 200);
        else
            return response()->json(['error' => true, 'message' => 'Unauthorised', 'data' => []], 401);
    }
}
