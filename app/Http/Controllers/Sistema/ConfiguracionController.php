<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\CategoriaMaquinaria;
use App\Models\Extras;
use App\Models\Maquinaria;
use App\Models\MaquinariaMantenimiento;
use App\Models\Membresia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    private function getExtras(){
        return Extras::value('tema');
    }

    public function indexMembresia()
    {
        $predeterminado = $this->getExtras();

        return view('backend.admin.configuracion.membresia.vistamembresia', compact('predeterminado'));
    }


    public function tablaMembresia()
    {
        $arrayMembresias = Membresia::orderBy('nombre', 'ASC')->get()
        ->map(function($item){

            $item->precioFormat = '$' . number_format((float)$item->precio, 2, '.', ',');

            return $item;
        });

        return view('backend.admin.configuracion.membresia.tablamembresia', compact('arrayMembresias'));
    }


    public function nuevoMembresia(Request $request)
    {
        $regla = array(
            'nombre' => 'required',
            'precio' => 'required',
            'duracion' => 'required',
        );

        // descripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        $registro = new Membresia();
        $registro->nombre = $request->nombre;
        $registro->precio = $request->precio;
        $registro->duracion_dias = $request->duracion;
        $registro->descripcion = $request->descripcion;

        if($registro->save()){
            return ['success' => 1];
        }else{
            return ['success' => 99];
        }
    }


    public function informacionMembresia(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($infoMembresia = Membresia::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $infoMembresia];
        }else{
            return ['success' => 2];
        }
    }


    public function editarMembresia(Request $request)
    {
        $regla = array(
            'nombre' => 'required',
            'precio' => 'required',
            'duracion' => 'required',
        );

        // descripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        Membresia::where('id', $request->id)->update([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'duracion_dias' => $request->duracion,
            'descripcion' => $request->descripcion,
        ]);

        return ['success' => 1];
    }






    //*=====================  CATEGORIAS MAQUINARIA ==============================



    public function indexCategoriaMaquinaria()
    {
        $predeterminado = $this->getExtras();

        return view('backend.admin.configuracion.categoriamaquina.vistacategoriamaquinaria', compact('predeterminado'));
    }

    public function tablaCategoriaMaquinaria()
    {
        $arrayCategorias = CategoriaMaquinaria::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.configuracion.categoriamaquina.tablacategoriamaquinaria ', compact('arrayCategorias'));
    }


    public function nuevoCategoriaMaquinaria(Request $request)
    {
        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        $registro = new CategoriaMaquinaria();
        $registro->nombre = $request->nombre;

        if($registro->save()){
            return ['success' => 1];
        }else{
            return ['success' => 99];
        }
    }


    public function informacionCategoriaMaquinaria(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($infoCategoria = CategoriaMaquinaria::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $infoCategoria];
        }else{
            return ['success' => 2];
        }
    }


    public function editarCategoriaMaquinaria(Request $request)
    {
        $regla = array(
            'nombre' => 'required',
        );


        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        CategoriaMaquinaria::where('id', $request->id)->update([
            'nombre' => $request->nombre,
        ]);

        return ['success' => 1];
    }






    //*=====================  MAQUINARIA ==============================



    public function indexMaquinaria()
    {
        $predeterminado = $this->getExtras();

        $arrayCategorias = CategoriaMaquinaria::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.configuracion.maquinaria.vistamaquinaria', compact('predeterminado', 'arrayCategorias'));
    }

    public function tablaMaquinaria()
    {
        $arrayMaquinarias = Maquinaria::orderBy('nombre', 'ASC')->get()->map(function($item){

            $infoCategoria = CategoriaMaquinaria::where('id', $item->id_maqui_categoria)->first();
            $item->nombreCategoria = $infoCategoria->nombre;

            $item->precioFormat = '$' . number_format((float)$item->precio, 2, '.', ',');

            return $item;
        });


        return view('backend.admin.configuracion.maquinaria.tablamaquinaria', compact('arrayMaquinarias'));
    }


    public function nuevoMaquinaria(Request $request)
    {
        $regla = array(
            'categoria' => 'required',
            'nombre' => 'required',
        );

        // precio, descripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $registro = new Maquinaria();
        $registro->id_maqui_categoria  = $request->categoria;
        $registro->fecha  = now('America/El_Salvador')->toDateString();
        $registro->nombre  = $request->nombre;
        $registro->precio  = !empty($request->precio) ? $request->precio : 0;
        $registro->descripcion  = $request->descripcion;

        if($registro->save()){
            return ['success' => 1];
        }else{
            return ['success' => 99];
        }
    }


    public function informacionMaquinaria(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($infoMaquinaria = Maquinaria::where('id', $request->id)->first()){

            $arrayCategoria = CategoriaMaquinaria::orderBy('nombre', 'ASC')->get();

            return ['success' => 1, 'info' => $infoMaquinaria,  'categoria' => $arrayCategoria];
        }else{
            return ['success' => 2];
        }
    }


    public function editarMaquinaria(Request $request)
    {
        $regla = array(
            'id' => 'required',
            'categoria' => 'required',
            'nombre' => 'required',
        );

        // precio, descripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        Maquinaria::where('id', $request->id)->update([
            'id_maqui_categoria' => $request->categoria,
            'nombre' => $request->nombre,
            'precio' => !empty($request->precio) ? $request->precio : 0,
            'descripcion' => $request->descripcion
        ]);

        return ['success' => 1];
    }


    public function borrarMaquinaria(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        // borrar mantenimientos asociados
        MaquinariaMantenimiento::where('id_maquinarias', $request->id)->delete();
        Maquinaria::where('id', $request->id)->delete();

        return ['success' => 1];
    }


    // ================== HISTORIAL DE MANTENIMIENTOS ======================

    public function indexHistorialMantenimientos($idequipo)
    {
        $predeterminado = $this->getExtras();

        return view('backend.admin.configuracion.maquinaria.mantenimiento.vistamantenimiento', compact('idequipo', 'predeterminado'));
    }


    public function tablaHistorialMantenimientos($idequipo)
    {
        $arrayHistorial = MaquinariaMantenimiento::where('id_maquinarias', $idequipo)->get()->map(function ($item) {

            $item->fechaFormat = date("d-m-Y", strtotime($item->fecha));
            $item->precioFormat = '$' . number_format((float)$item->precio, 2, '.', ',');

            return $item;
        });

        return view('backend.admin.configuracion.maquinaria.mantenimiento.tablamantenimiento', compact('arrayHistorial'));
    }



    public function nuevoMaquinariaMantenimientos(Request $request)
    {
        $regla = array(
            'idmaquinaria' => 'required',
            'fecha' => 'required',
            'precio' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $registro = new MaquinariaMantenimiento();
        $registro->id_maquinarias  = $request->idmaquinaria;
        $registro->fecha  = $request->fecha;
        $registro->precio  = $request->precio;
        $registro->descripcion = $request->descripcion;

        if($registro->save()){
            return ['success' => 1];
        }else{
            return ['success' => 99];
        }
    }



    public function borrarHistorialMantenimientos(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        MaquinariaMantenimiento::where('id', $request->id)->delete();

        return ['success' => 1];
    }










}
