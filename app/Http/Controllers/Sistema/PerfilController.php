<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Extras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class PerfilController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    private function getExtras(){
        return Extras::value('tema');
    }
    public function indexEditarPerfil(){
        $usuario = auth()->user();
        $predeterminado = $this->getExtras();

        return view('backend.admin.perfil.vistaperfil', compact('usuario', 'predeterminado'));
    }

    public function editarUsuario(Request $request){

        $regla = array(
            'password' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0];}

        $usuario = auth()->user();

        Administrador::where('id', $usuario->id)
            ->update(['password' => bcrypt($request->password)]);

        return ['success' => 1];
    }
}
