<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        Config::set('jwt.user', 'App\Models\Usuario');
        Config::set('auth.defaults.guard', 'api');
        Config::set('auth.defaults.passwords', 'users');
    }

    public function login(Request $request)
    {
        $token = Auth::shouldUse('api');
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

    public function register()
    {
        $validador = Validator::make(request()->all(), [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validador->fails()) {
            return response()->json($validador->errors()->toJson(), 400);
        }

        $usuario = Usuario::create([
            'nombres' => request('nombres'),
            'apellidos' => request('apellidos'),
            'identificacion' => request('identificacion'),
            'usuario' => request('identificacion'),
            'password' => bcrypt(request('password')),
        ]);

        $usuario->save();

        $token = $this->guard()->login($usuario);

        return response()->json(['message' => 'User created successfully', 'token' => $token], 201);
    }

    /**
     * Logout usuario
     *
     * @return void
     */

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out Successfully.'
        ], 200);
    }

    public function me()
    {
        return response()->json(
            Patient::firstWhere('identifier', auth()->user()->usuario)
        );
    }

    public function refresh()
    {
        if ($token = $this->guard()->refresh()) {
            return response()->json()
                ->json(['status' => 'successs'], 200)
                ->header('Authorization', $token);
        }
        return response()->json(['error' => 'refresh_token_error'], 401);
    }

    public function renew()
    {
        $token = Auth::shouldUse('api');
        try {
            if (!$token = $this->guard()->refresh()) {
                return response()->json(['error' => 'refresh_token_error'], 401);
            }
            $user = auth()->user();
            $user = Usuario::with(
                [
                    'person' => function ($query) {
                        $query->with([
                            'companies',
                            'companyWorked',
                            'dispensingPoint',
                            'dispensingPoints' => function ($q) {
                                $q->with('municipality');
                            }
                        ]);
                    },
                    'board' => function ($q) {
                        $q->select('*');
                    },
                    'task' => function ($q) {
                        $q->select('*');
                    },

                ]
            )->find($user->id);

            return response()
                ->json(['status' => 'successs', 'token' => $token, 'user' => $user], 200)
                ->header('Authorization', $token);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'refresh_token_error' . $th->getMessage()], 401);
        }
    }

    private function guard()
    {
        return Auth::guard();
    }

    protected function respondWithToken($token)
    {
        auth()->factory()->getTTL() * 60;

        return $token;
    }

    public function changePassword(Request $request)
    {
        $pattern = '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\'":\\\\|,.<>\/?])[A-Za-z\d!@#$%^&*()_+\-=\[\]{};\'":\\\\|,.<>\/?]{8,}$';

        $validator = Validator::make($request->all(), [
            'newPassword' => [
                'required',
                'string',
                'min:8',
                "regex:/$pattern/",
            ],
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 400);
        }

        if (!auth()->user()) {
            return $this->error('User not found', 400);
        }
        $password = auth()->user()->password;
        if (Hash::check($request->input('newPassword'), $password)) {
            return $this->error('La contraseña nueva no puede ser la misma que la actual', 400);
        }
        $user = Usuario::find(auth()->user()->id);
        $user->password = Hash::make($request->input('newPassword'));
        $user->change_password = 0;
        $user->save();
        return $this->success('Contraseña actualizada correctamente');
    }

    public function restorePassword($id)
    {
        $user = auth()->user()->id;
        if ($user === 1) {
            $userToChange = Usuario::where('person_id', $id)->first();
            $userToChange->change_password = 1;
            $userToChange->password = Hash::make($userToChange->usuario);
            $userToChange->save();
            return $this->success('El funcionario puede ingresar con su número de documento como contraseña y al iniciar sesión se le solicitará cambiar su contraseña');
        } else {
            return $this->error('No tiene permisos para realizar esta operación', 400);
        }
    }
}
