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
        'cedula' => 'required|unique:personas,cedula|regex:"^([0-9]{10,10})"|max:10|min:10',
        'nombre' => 'required',
        'apellido' => 'required',
        'name' => 'required|unique:users,name',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
    );
    private $messagesRegister = array(
        'cedula.required' => 'La cedula es requerida',
        'cedula.unique' => 'Numero de Cedula no valido',
        'cedula.regex' => 'La cedula debe terner 10 digitos',
        'cedula.min' => 'La cedula debe terner 10 digitos',
        'cedula.max' => 'La cedula debe terner 10 digitos',
        'nombre.required' => 'El nombre es requerido',
        'apellido.required' => 'El apellido es requerido',
        'name.required' => 'El name es requerido',
        'name.unique' => 'El name ingresado ya esta en uso',
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

            $persona = Persona::create([
                'cedula' => $request->cedula,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
            ]);

            $user = new User([
                'persona_id' => $persona->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole('Cliente');
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
                    'message' => 'Usuario no Registrado o Password Incorrecto',
                ], 404);
            }


        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Loguearse',
            ], 500);
        }
    }

    public function userProfile(){
        $user = auth()->user();
        $usuario = DB::table('users')
        ->where('users.id', '=', $user->id)
        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->select('users.name', 'users.email', 'roles.name as rol')
        ->get();
        return response()->json([
            'message' => 'Perfil de Usuario',
            'User' => $usuario[0],
            /* "usuario"=>$user, */
        ]);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout con Exito'
        ]);
    }
}
