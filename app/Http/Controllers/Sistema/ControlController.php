<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Extras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControlController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function indexRedireccionamiento(){
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.roles.index');
        }

        if ($user->hasRole('editor')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('no.permisos.index');
    }

    public function indexSinPermiso(){
        return view('errors.403');
    }


    public function actualizarTema(Request $request)
    {
        $data = $request->validate([
            'tema' => 'required|in:0,1', // 0=light, 1=dark
        ]);

        // Si lo guardás en la tabla 'extras' id=1:
        Extras::whereKey(1)->update(['tema' => $data['tema']]);

        return response()->json(['ok' => true, 'tema' => (int)$data['tema']]);
    }


}
