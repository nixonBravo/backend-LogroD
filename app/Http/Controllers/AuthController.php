<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Persona;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $rulesRegister = array(
        'cedula' => 'required|numeric|digits:10|unique:users,cedula',
        'nombre' => 'required',
        'apellido' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
    );
    private $messagesRegister = array(
        'cedula.required' => 'La cedula es requerida',
        'cedula.numeric' => 'La cedula debe ser numerica',
        'cedula.digits' => 'La cedula debe tener 10 digitos',
        'cedula.unique' => 'La cedula ingresada es incorrecta',
        'nombre.required' => 'El nombre es requerido',
        'apellido.required' => 'El apellido es requerido',
        'email.required' => 'El email es requerido',
        'email.email' => 'El email debe ser tipo email',
        'email.unique' => 'El email ingresado ya esta en uso',
        'password.required' => 'El password es requerido',
        'password.min' => 'El password debe tener minimo 8 caracteres',
    );

    private $rulesLogin = array(
        'email' => 'required|email',
        'password' => 'required',
    );
    private $messagesLogin = array(
        'email.required' => 'El email es requerido',
        'email.email' => 'El email debe ser tipo email',
        'password.required' => 'El password es requerido',
    );

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rulesRegister, $this->messagesRegister);

            if ($validator->fails()) {
                $messages = $validator->messages();
                return response()->json([
                    'messages' => $messages,
                ], 422);
            }

            $user = new User([
                'cedula' => $request->cedula,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole('Cliente');
            //$user->createAsStripeCustomer();
            $user->save();

            return response()->json([
                'message' => 'Registrado con Exito',
                //'access_token' => $user->createToken('auth_token')->plainTextToken;
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Registrarse',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rulesLogin, $this->messagesLogin);

            if ($validator->fails()) {
                $messages = $validator->messages();
                return response()->json([
                    'messages' => $messages,
                ], 422);
            }

            $user = User::where('email', '=', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no Registrado o Email incorrecto'
                ], 203);
            }

            if (isset($user->id)) {
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken('auth_token')->plainTextToken;
                    return response()->json([
                        'message' => 'Logueado con Exito',
                        'access_token' => $token,
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Password Incorrecto',
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'Password Incorrecto',
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Loguearse',
            ], 500);
        }
    }

    public function userProfile()
    {
        try {
            $user = auth()->user();
            $usuario = DB::table('users')
                ->where('users.id', '=', $user->id)
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('users.nombre', 'users.apellido', 'users.email', 'roles.name as rol')
                ->get();
            return response()->json([
                'message' => 'Perfil de Usuario',
                'User' => $usuario[0],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo ver el perfil',
            ], 500);
        }
    }

    public function logout()
    {
        try {
            auth()->user()->tokens()->delete();
            return response()->json([
                'message' => 'Logout con Exito'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Logout con Exito'
            ], 500);
        }
    }
}
