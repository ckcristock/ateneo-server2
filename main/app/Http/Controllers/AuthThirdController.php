<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyPerson;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Config;

class AuthThirdController extends Controller
{
    use ApiResponser;
    /**
     * Login usuario y retornar el token
     * @return token
     */
    public function __construct()
    {
        Config::set('jwt.user', 'App\Models\ThirdPartyPerson');
        Config::set('auth.defaults.guard', 'third');
        Config::set('auth.defaults.passwords', 'users');
    }

    public function login(Request $request)
    {
        $token = Auth::shouldUse('third');
        try {
            $credentials = $request->only('user', 'password');
            $data['usuario'] = $credentials['user'];
            $data['password'] = $credentials['password'];
            if (
                !$token = JWTAuth::attempt([
                    'usuario' => $data['usuario'],
                    'password' => $data['password']
                ])
            ) {
                return response()->json(['error' => 'Unauthoriz55ed'], 401);
            }

            return response()->json([
                'status' => 'success',
                'token' => $this
                    ->respondWithToken($token)
            ], 200)
                ->header('Authorization', $token)
                ->withCookie(
                    'token',
                    $token,
                    config('jwt.ttl'),
                    '/'
                );
        } catch (\Throwable $th) {
            return $this->errorResponse([$th->getMessage(), $th->getFile(), $th->getLine()]);
        }
    }

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out Successfully.'
        ], 200);
    }

    /**
     * Obtener el usuario autenticado
     *
     * @return ThirdPartyPerson
     */


    /**
     * Refrescar el token por uno nuevo
     *
     * @return token
     */

    public function refresh()
    {
        Config::set('auth.defaults.guard', 'third');
        if ($token = $this->guard()->refresh()) {
            return response()->json()
                ->json(['status' => 'successs'], 200)
                ->header('Authorization', $token);
        }
        return response()->json(['error' => 'refresh_token_error'], 401);
    }

    public function renew()
    {
        $token = Auth::shouldUse('third');
        try {
            if (!$token = $this->guard()->refresh()) {
                return response()->json(['error' => 'refresh_token_error'], 401);
            }
            $user = auth()->user();
            $user = ThirdPartyPerson::with('laboratory')->find($user->id);
            return response()
                ->json(['status' => 'successs', 'token' => $token, 'user' => $user], 200)
                ->header('Authorization', $token);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'refresh_token_error' . $th->getMessage()], 401);
        }
    }
    /**
     * Retornar el guard
     *
     * @return Guard
     */

    private function guard()
    {
        return Auth::guard();
    }

    protected function respondWithToken($token)
    {
        auth()->factory()->getTTL() * 60;
        return $token;
    }
}
