<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct(){
        $this->middleware('guest', ['except' => ['logout']]);
    }

    public function vistaLoginForm(){
        return view('frontend.login.vistalogin');
    }

    public function login(Request $request){

        $rules = array(
            'usuario' => 'required',
            'password' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ( $validator->fails()){
            return ['success' => 0];
        }

        // si ya habia iniciado sesion, redireccionar
        if (Auth::check()) {
            return ['success'=> 1, 'ruta'=> route('admin.panel')];
        }

        $credenciales = [
            'email'    => $request->input('usuario'), // ← mapeo a email
            'password' => $request->input('password'),
        ];

        if (Auth::guard('admin')->attempt($credenciales, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return ['success'=> 1, 'ruta'=> route('admin.panel')];
        }

        return ['success' => 2];
    }

    public function logout(Request $request){
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login.admin');
    }
}
