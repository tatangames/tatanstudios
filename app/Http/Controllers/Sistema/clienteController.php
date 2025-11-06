<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Extras;
use App\Models\Membresia;
use App\Models\MembresiaAbono;
use App\Models\Usuario;
use App\Models\UsuarioMembresia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class clienteController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    private function getExtras(){
        return Extras::value('tema');
    }

    public function vistaNuevoCliente()
    {
        $predeterminado = $this->getExtras();

        $arrayMembresias = Membresia::orderBy('nombre', 'asc')->get()->map(function($item){

            $precioFormat = '$' . number_format((float)$item->precio, 2, '.', ',');

            $item->nombreCompleto = $item->nombre . ' | ' . $precioFormat . ' | Días: ' . $item->duracion_dias;

            return $item;
        });

        return view('backend.admin.cliente.vistanuevocliente', compact('predeterminado', 'arrayMembresias'));
    }


    public function registrarCliente(Request $request)
    {
        $regla = array(
            'nombre' => 'required',
            'sexo' => 'required',
            'fechanac' => 'required',
            'membresia' => 'required',
        );

        // correo, telefono, abono, nombreEmergencia, telefonoEmergencia, condicionEmergencia

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();

        try {

            $tz = 'America/El_Salvador';

            $user = new Usuario();
            $user->fecha_registrado = Carbon::now($tz)->toDateString();
            $user->nombre = $request->nombre;
            $user->imagen = null;
            $user->correo = $request->correo;
            $user->telefono = $request->telefono;
            $user->fecha_nacimiento = $request->fechanac;
            $user->sexo = $request->sexo;
            $user->emergencia_nombre = $request->nombreEmergencia;
            $user->emergencia_telefono = $request->telefonoEmergencia;
            $user->condicion_medica = $request->condicionEmergencia;
            $user->save();

            /// ========

            // 2) Info membresía base
            $infoMembresia = Membresia::findOrFail($request->membresia);

            // Fecha de inicio: usar la que viene o hoy
            $fechaInicio = $request->filled('fecha')
                ? Carbon::parse($request->fecha, $tz)->startOfDay()
                : Carbon::now($tz)->startOfDay();

            // Fin inclusivo: duracion_dias - 1
            $fechaFin = $fechaInicio->copy()->addDays(((int)$infoMembresia->duracion_dias) - 1);

            // 3) Normalizar montos
            $precio = (float) $infoMembresia->precio;
            $rawAbono = $request->input('abono');

            // Si no viene nada, tomamos el precio completo de la membresía
            if ($rawAbono === null || $rawAbono === '') {
                $abono = (float) $infoMembresia->precio;
            } else {
                $abono = (float) $rawAbono;
            }

            // Normalizamos límites
            if ($abono < 0) {
                $abono = 0.0;
            } elseif ($abono > (float) $infoMembresia->precio) {
                $abono = (float) $infoMembresia->precio;
            }


            $solvente = ($abono >= $precio) ? 1 : 0;

            // 4) Guardar membresía (primera → is_actual = 1)
            $userMembresia = new UsuarioMembresia();
            $userMembresia->id_usuarios      = $user->id;
            $userMembresia->id_membresia     = $infoMembresia->id;
            $userMembresia->fecha_registrado = Carbon::now($tz)->toDateString();
            $userMembresia->nombre           = $infoMembresia->nombre;
            $userMembresia->precio           = $precio;
            $userMembresia->duracion_dias    = (int) $infoMembresia->duracion_dias;
            $userMembresia->fecha_inicio     = $fechaInicio->toDateString();
            $userMembresia->fecha_fin        = $fechaFin->toDateString();
            $userMembresia->solvente         = $solvente;
            $userMembresia->is_actual        = 1;
            $userMembresia->save();

            // 5) Registrar abono SIEMPRE (aunque sea 0 para trazabilidad)
            MembresiaAbono::create([
                'id_usuario_membresia' => $userMembresia->id,
                'fecha_pago'           => Carbon::now($tz)->toDateString(),
                'monto'                => $abono,
            ]);

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    //***********************************************************************************************


    public function indexListadoClientes()
    {
        $predeterminado = $this->getExtras();

        $arrayMembresias = Membresia::orderBy('nombre', 'ASC')->get()
            ->map(function($item){

                $precioFormat = '$' . number_format((float)$item->precio, 2, '.', ',');

                $item->nombreCompleto = $item->nombre . ' | ' . $precioFormat . ' | Días: ' . $item->duracion_dias;

                return $item;
            });

        return view('backend.admin.cliente.listado.vistaclientes', compact('predeterminado', 'arrayMembresias'));
    }

    public function tablaListadoClientes($filtroDias = 0)
    {
        $filtroDias = (int) $filtroDias;
        $tz = 'America/El_Salvador';

        $arrayClientes = Usuario::orderBy('fecha_registrado', 'desc')
            ->get()
            ->map(function($item) use ($tz) {
                $item->fechaRegistro = date("d-m-Y", strtotime($item->fecha_registrado));
                $item->genero = ($item->sexo == 1) ? 'Masculino' : 'Femenino';

                $ultimaMembresia = UsuarioMembresia::where('id_usuarios', $item->id)
                    ->where('is_actual', 1)
                    ->first();

                $item->nombreMembresia      = $ultimaMembresia->nombre ?? '—';
                $item->fechaInicioMembresia = $ultimaMembresia ? date("d-m-Y", strtotime($ultimaMembresia->fecha_inicio)) : '—';

                if ($ultimaMembresia) {
                    $inicio = Carbon::parse($ultimaMembresia->fecha_inicio, $tz)->startOfDay();
                    $finEnd = Carbon::parse($ultimaMembresia->fecha_fin, $tz)->endOfDay();   // estado real
                    $finDay = Carbon::parse($ultimaMembresia->fecha_fin, $tz)->startOfDay(); // días enteros
                    $hoy    = Carbon::today($tz);
                    $ahora  = Carbon::now($tz);

                    $diasRestantes = $hoy->diffInDays($finDay, false);

                    if ($ahora->gt($finEnd))      $estado = 'Vencido';
                    elseif ($diasRestantes === 0) $estado = 'Hoy';
                    elseif ($diasRestantes <= 5)  $estado = 'Por vencer';
                    else                          $estado = 'Activo';

                    $item->fechaSalida   = date("d-m-Y", strtotime($ultimaMembresia->fecha_fin));
                    $item->diasRestantes = $diasRestantes;
                    $item->estado        = $estado;
                    $item->diasTotales   = $inicio->diffInDays($finDay) + 1;

                    // Solvencia
                    $um = UsuarioMembresia::withSum('abonos', 'monto')->find($ultimaMembresia->id);
                    $precio      = (float) $ultimaMembresia->precio;
                    $pagoInicial = (float) ($um->pago ?? 0);
                    $totalAbonos = (float) ($um->abonos_sum_monto ?? 0);
                    $totalPagado = $pagoInicial + $totalAbonos;

                    $adeudo = max($precio - $totalPagado, 0);
                    $item->adeudo   = '$' . number_format((float)$adeudo, 2, '.', ',');
                    $item->solvente = ($totalPagado >= $precio) ? 1 : 0;
                } else {
                    $item->fechaSalida   = '—';
                    $item->diasRestantes = null;
                    $item->estado        = 'Sin membresía';
                    $item->diasTotales   = null;
                    $item->adeudo        = null;
                    $item->solvente      = 0;
                }

                return $item;
            });

        // Aplica filtro solo si > 0: próximos a vencer entre hoy (0) y X días
        if ($filtroDias > 0) {
            $arrayClientes = $arrayClientes->filter(function($it) use ($filtroDias) {
                return $it->diasRestantes !== null
                    && $it->diasRestantes <= $filtroDias; // incluye vencidos (negativos) y hasta el día X
            })->values();
        }

        // Devuelve la tabla (parcial)
        return view('backend.admin.cliente.listado.tablaclientes', compact('arrayClientes'));
    }


    public function informacionCliente(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($infocliente = Usuario::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $infocliente];
        }else{
            return ['success' => 2];
        }
    }


    public function editarCliente(Request $request)
    {
        $regla = array(
            'id' => 'required',
            'nombre' => 'required',
            'fechanac' => 'required',
            'sexo' => 'required',
        );

        // correo, telefono, nombreEmergencia, telefonoEmergencia, condicionEmergencia, imagen

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if ($request->hasFile('imagen')) {

            $cadena = Str::random(15);
            $tiempo = microtime();
            $union = $cadena . $tiempo;
            $nombre = str_replace(' ', '_', $union);

            $extension = '.' . $request->imagen->getClientOriginalExtension();
            $nombreFoto = $nombre . strtolower($extension);
            $avatar = $request->file('imagen');
            $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

            if ($upload) {

                $infoCliente = Usuario::where('id', $request->id)->first();
                $imagenOld = $infoCliente->imagen;
                if (!empty($imagenOld) && Storage::disk('archivos')->exists($imagenOld)) {
                    Storage::disk('archivos')->delete($imagenOld);
                }

                Usuario::where('id', $request->id)->update([
                    'nombre' => $request->nombre,
                    'imagen' => $nombreFoto,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono,
                    'fecha_nacimiento' => $request->fechanac,
                    'sexo' => $request->sexo,
                    'emergencia_nombre' => $request->nombreEmergencia,
                    'emergencia_telefono' => $request->telefonoEmergencia,
                    'condicion_medica' => $request->condicionEmergencia,
                ]);

                return ['success' => 1];

            } else {
                // error al subir imagen
                return ['success' => 99];
            }
        } else {
            Usuario::where('id', $request->id)->update([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fechanac,
                'sexo' => $request->sexo,
                'emergencia_nombre' => $request->nombreEmergencia,
                'emergencia_telefono' => $request->telefonoEmergencia,
                'condicion_medica' => $request->condicionEmergencia,
            ]);
        }


        return ['success' => 1];
    }






    // ========================= CLIENTES - DEUDAS ================================================


    public function indexListadoClientesConDeuda()
    {
        $predeterminado = $this->getExtras();

        return view('backend.admin.deuda.vistaclientedeuda', compact('predeterminado'));
    }

    public function tablaListadoClientesConDeuda()
    {
        $tz = 'America/El_Salvador';

        $usuarios = Usuario::orderBy('fecha_registrado', 'desc')->get();

        $arrayClientes = $usuarios->map(function ($item) use ($tz) {

            $item->fechaRegistro = date("d-m-Y", strtotime($item->fecha_registrado));
            $item->genero = ($item->sexo == 1) ? 'Masculino' : 'Femenino';

            $ultimaMembresia = UsuarioMembresia::where('id_usuarios', $item->id)
                ->where('is_actual', 1)
                ->first();

            $item->nombreMembresia      = $ultimaMembresia->nombre ?? '—';
            $item->fechaInicioMembresia = $ultimaMembresia ? date("d-m-Y", strtotime($ultimaMembresia->fecha_inicio)) : '—';

            if ($ultimaMembresia) {
                $inicio = Carbon::parse($ultimaMembresia->fecha_inicio, $tz)->startOfDay();
                $fin    = Carbon::parse($ultimaMembresia->fecha_fin, $tz)->endOfDay();
                $hoy    = Carbon::today($tz);
                $ahora  = Carbon::now($tz);

                // Para cálculo exacto de días restantes (solo fechas)
                $finInicioDia = Carbon::parse($ultimaMembresia->fecha_fin, $tz)->startOfDay();
                $diasRestantes = $hoy->diffInDays($finInicioDia, false);

                // Estado correcto
                if ($ahora->gt($fin)) {
                    $estado = 'Vencido';
                } elseif ($diasRestantes === 0) {
                    $estado = 'Hoy';
                } elseif ($diasRestantes <= 5) {
                    $estado = 'Por vencer';
                } else {
                    $estado = 'Activo';
                }

                $item->fechaSalida   = date("d-m-Y", strtotime($ultimaMembresia->fecha_fin));
                $item->diasRestantes = $diasRestantes;
                $item->estado        = $estado;
                $item->diasTotales   = $inicio->diffInDays($finInicioDia) + 1;

                // Solvencia
                $um = UsuarioMembresia::withSum('abonos', 'monto')->find($ultimaMembresia->id);

                $precio      = (float) $ultimaMembresia->precio;
                $pagoInicial = (float) ($um->pago ?? 0);
                $totalAbonos = (float) ($um->abonos_sum_monto ?? 0);
                $totalPagado = $pagoInicial + $totalAbonos;

                $adeudoNum = max($precio - $totalPagado, 0);
                $item->adeudo_num = $adeudoNum;
                $item->adeudo     = '$' . number_format($adeudoNum, 2, '.', ',');
                $item->solvente   = ($totalPagado >= $precio) ? 1 : 0;
            }
            else {
                // Sin membresía: no contar como deuda
                $item->fechaSalida   = '—';
                $item->diasRestantes = null;
                $item->estado        = 'Sin membresía';
                $item->diasTotales   = null;
                $item->adeudo        = null;
                $item->adeudo_num    = null;
                $item->solvente      = 0;
            }

            return $item;
        });

        // Solo clientes con deuda (> 0) y con membresía registrada
        $arrayClientes = $arrayClientes
            ->filter(fn ($i) => isset($i->adeudo_num) && $i->adeudo_num > 0 && $i->solvente == 0)
            ->values();


        return view('backend.admin.deuda.tablaclientedeuda', compact('arrayClientes'));
    }


    public function informacionClienteConDeuda(Request $request)
    {
        $request->validate([
            'id' => ['required','integer'], // id del usuario
        ]);

        $tz = 'America/El_Salvador';

        // Cliente
        $cliente = Usuario::findOrFail($request->id);

        // Última membresía del cliente
        $um = UsuarioMembresia::where('id_usuarios', $cliente->id)
            ->where('is_actual', 1)
            ->first();

        // Si no tiene membresía, devolvemos estructura vacía
        if (!$um) {
            return response()->json([
                'ok' => true,
                'cliente' => [
                    'id'     => $cliente->id,
                    'nombre' => $cliente->nombre,
                ],
                'membresia' => null,
                'historial_abonos' => [],
                'total_pagado' => 0.0,
                'precio' => 0.0,
                'adeuda' => false,
                'adeudo' => 0.0,
                'estado_pago' => 'Sin membresía',
            ]);
        }

        // Historial de abonos (tabla: membresia_abono)
        $abonos = MembresiaAbono::where('id_usuario_membresia', $um->id)
            ->orderBy('fecha_pago', 'asc')
            ->get(['id','fecha_pago','monto'])
            ->map(function ($a) use ($tz) {
                return [
                    'id'         => (int) $a->id,
                    'fecha_pago' => Carbon::parse($a->fecha_pago, $tz)->format('d-m-Y'),
                    'monto'      => (float) $a->monto,
                ];
            });

        // Cálculo de solvencia
        $precio       = (float) ($um->precio ?? 0);
        $pagoInicial  = (float) ($um->pago ?? 0); // si guardas un pago inicial en la membresía
        $totalAbonos  = (float) $abonos->sum('monto');
        $totalPagado  = $pagoInicial + $totalAbonos;
        $adeudoNum    = max($precio - $totalPagado, 0.0);
        $solvente     = $totalPagado >= $precio;

        // Info de fechas/días restantes (opcional, útil en UI)
        $inicio = Carbon::parse($um->fecha_inicio, $tz)->startOfDay();
        $fin    = Carbon::parse($um->fecha_fin, $tz)->endOfDay();
        $hoy    = Carbon::today($tz);
        $diasRestantes = $hoy->diffInDays($fin, false);

        return response()->json([
            'success' => 1,
            'cliente' => [
                'id'     => $cliente->id,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono ?? null,
            ],
            'membresia' => [
                'id'            => $um->id,
                'nombre'        => $um->nombre,
                'precio'        => $precio,
                'pago_inicial'  => $pagoInicial,
                'fecha_inicio'  => $inicio->format('d-m-Y'),
                'fecha_fin'     => $fin->format('d-m-Y'),
                'dias_restantes'=> $diasRestantes,
            ],
            'historial_abonos' => $abonos,      // ← array con {id, fecha_pago, monto}
            'total_pagado'     => (float) $totalPagado,
            'precio'           => (float) $precio,
            'adeuda'           => !$solvente,   // true si aún debe
            'adeudo'           => (float) $adeudoNum, // cuánto debe
            'estado_pago'      => $solvente ? 'Solvente' : 'Adeuda',
        ]);
    }


    public function abonarClienteConDeuda(Request $request)
    {
        $v = Validator::make($request->all(), [
            'id_membresia' => 'required|integer',
            'monto' => 'required|numeric|min:0.01|max:9000000',
        ]);

        if ($v->fails()) {
            return ['success' => 0];
        }

        $id = (int) $request->id_membresia;

        $monto = round((float) $request->monto, 2);

        $um = UsuarioMembresia::find($id);
        if (!$um) return ['success' => 0];

        $precio = (float) $um->precio;
        $pagoInicial = (float) ($um->pago ?? 0);
        $totalAbonos = (float) MembresiaAbono::where('id_usuario_membresia', $id)->sum('monto');
        $totalPagado = $pagoInicial + $totalAbonos;
        $pendiente = max($precio - $totalPagado, 0);

        if ($monto > $pendiente) {
            return ['success' => 2, 'pendiente' => number_format($pendiente, 2)];
        }

        MembresiaAbono::create([
            'id_usuario_membresia' => $id,
            'fecha_pago' => now('America/El_Salvador')->toDateString(),
            'monto' => $monto,
        ]);

        // Recalcular total pagado después de registrar
        $totalAbonos = MembresiaAbono::where('id_usuario_membresia', $id)->sum('monto');
        $totalPagado = $pagoInicial + $totalAbonos;

        // Si ya completó el precio, marcar como solvente
        if ($totalPagado >= $precio) {
            $um->solvente = 1;
            $um->save();
        }

        return ['success' => 1];
    }



    public function nuevaMembresiaCliente(Request $request)
    {
        $regla = array(
            'id' => 'required',
            'membresia' => 'required',
            'fecha' => 'required', // desde cuando iniciar membresia
        );

        // abono

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $tz = 'America/El_Salvador';

        try {
            DB::beginTransaction();

            $userId        = (int) $request->id;
            $infoMembresia = Membresia::findOrFail($request->membresia);

            // Fechas (usar la fecha enviada como inicio)
            $fechaInicio = Carbon::parse($request->fecha, $tz)->startOfDay();
            // Si guardas DATE, no hace falta endOfDay; además hacemos fin inclusivo: duracion_dias - 1
            $fechaFin    = $fechaInicio->copy()->addDays(((int)$infoMembresia->duracion_dias) - 1);

            // Normalización de montos
            $precio = (float) $infoMembresia->precio;
            $rawAbono = $request->input('abono');

            // Si no viene nada, tomamos el precio completo de la membresía
            if ($rawAbono === null || $rawAbono === '') {
                $abono = (float) $infoMembresia->precio;
            } else {
                $abono = (float) $rawAbono;
            }

            // Normalizamos límites
            if ($abono < 0) {
                $abono = 0.0;
            } elseif ($abono > (float) $infoMembresia->precio) {
                $abono = (float) $infoMembresia->precio;
            }

            $solvente = $abono >= $precio ? 1 : 0;

            // 1) Cerrar cualquier membresía actual previa de este usuario
            UsuarioMembresia::where('id_usuarios', $userId)
                ->where('is_actual', 1)
                ->update(['is_actual' => 0]);

            // 2) Crear nueva membresía como actual
            $userMembresia = new UsuarioMembresia();
            $userMembresia->id_usuarios      = $userId;
            $userMembresia->id_membresia     = $infoMembresia->id;
            $userMembresia->fecha_registrado = Carbon::now($tz)->toDateString();
            $userMembresia->nombre           = $infoMembresia->nombre;
            $userMembresia->precio           = $precio;
            $userMembresia->duracion_dias    = (int) $infoMembresia->duracion_dias;
            $userMembresia->fecha_inicio     = $fechaInicio->toDateString();
            $userMembresia->fecha_fin        = $fechaFin->toDateString();
            $userMembresia->solvente         = $solvente;
            $userMembresia->is_actual        = 1;   // ← vigente
            $userMembresia->save();

            // 3) Registrar abono SIEMPRE (aunque sea 0.00 para trazabilidad)
            MembresiaAbono::create([
                'id_usuario_membresia' => $userMembresia->id,
                'fecha_pago'           => Carbon::now($tz)->toDateString(),
                'monto'                => $abono,
            ]);

            DB::commit();

            return [
                'success'        => 1,
                'id_um'          => $userMembresia->id,
                'solvente'       => $solvente,
                'pendiente'      => number_format(max($precio - $abono, 0), 2, '.', ''),
                'fecha_inicio'   => date("d-m-Y", strtotime($userMembresia->fecha_inicio)),
                'fecha_fin'      => date("d-m-Y", strtotime($userMembresia->fecha_fin))
            ];


        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('nuevaMembresiaCliente error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['success' => 99];
        }
    }



    // -============= CLIENTES DE MEMBRESIAS VENCIDOS ====================

    public function indexListadoClientesVencidos()
    {
        $predeterminado = $this->getExtras();

        $arrayMembresias = Membresia::orderBy('nombre', 'ASC')->get()
            ->map(function($item){

                $precioFormat = '$' . number_format((float)$item->precio, 2, '.', ',');

                $item->nombreCompleto = $item->nombre . ' | ' . $precioFormat . ' | Días: ' . $item->duracion_dias;

                return $item;
            });

        return view('backend.admin.cliente.vencidos.vistaclientesvencidos', compact('predeterminado', 'arrayMembresias'));
    }

    public function tablaListadoClientesVencidos()
    {
        $tz   = 'America/El_Salvador';
        $hoy  = \Carbon\Carbon::today($tz);
        $ahora = \Carbon\Carbon::now($tz)->endOfDay()->toDateString();

        // Solo usuarios cuya membresía actual ya venció
        $usuarios = Usuario::whereHas('usuario_membresias', function ($q) use ($ahora) {
            $q->where('is_actual', 1)
                ->whereDate('fecha_fin', '<', $ahora);
        })
            ->orderBy('fecha_registrado', 'desc')
            ->get();

        $arrayClientes = $usuarios->map(function ($item) use ($tz, $hoy) {
            $item->fechaRegistro = date("d-m-Y", strtotime($item->fecha_registrado));
            $item->genero = ($item->sexo == 1) ? 'Masculino' : 'Femenino';

            $ultimaMembresia = UsuarioMembresia::where('id_usuarios', $item->id)
                ->where('is_actual', 1)
                ->first();

            if ($ultimaMembresia) {

                $inicio = \Carbon\Carbon::parse($ultimaMembresia->fecha_inicio, $tz)->startOfDay();
                $fin    = \Carbon\Carbon::parse($ultimaMembresia->fecha_fin, $tz)->endOfDay();

                // Dias restantes con signo (negativo si ya venció)
                $diasRestantes = $hoy->diffInDays($fin, false);

                // Si querés diasVencida NEGATIVO (p.ej. -3 días):
                $item->diasVencida = ($diasRestantes < 0) ? $diasRestantes : 0;

                // Si en algún lugar necesitás "días vencida" en valor POSITIVO, usá:
                // $diasVencidaAbs = $diasRestantes < 0 ? abs($diasRestantes) : 0;

                $item->diasTotales = $inicio->diffInDays($fin) + 1;

                $item->nombreMembresia      = $ultimaMembresia->nombre;
                $item->fechaInicioMembresia = date("d-m-Y", strtotime($ultimaMembresia->fecha_inicio));
                $item->fechaSalida          = date("d-m-Y", strtotime($ultimaMembresia->fecha_fin));
                $item->estado               = 'Vencido';

                // === SOLVENCIA ===
                $um = UsuarioMembresia::withSum('abonos', 'monto')->find($ultimaMembresia->id);

                $precio      = (float) $ultimaMembresia->precio;
                $pagoInicial = (float) ($um->pago ?? 0);
                $totalAbonos = (float) ($um->abonos_sum_monto ?? 0);
                $totalPagado = $pagoInicial + $totalAbonos;

                $adeudo = max($precio - $totalPagado, 0);
                $item->adeudo   = '$' . number_format((float)$adeudo, 2, '.', ',');
                $item->solvente = ($totalPagado >= $precio) ? 1 : 0;
            }

            return $item;
        })->values();

        return view('backend.admin.cliente.vencidos.tablaclientesvencidos', compact('arrayClientes'));
    }





    // ================== HISTORIAL DE MEMBRESIAS ======================

    public function indexHistorialMembresia($idcliente)
    {
        $predeterminado = $this->getExtras();

        return view('backend.admin.historial.membresia.vistahistorialmembresia', compact('idcliente', 'predeterminado'));
    }


    public function tablaHistorialMembresia($idcliente)
    {
        $arrayHistorial = UsuarioMembresia::where('id_usuarios', $idcliente)->orderBy('is_actual', 'desc')->get()->map(function ($item) {

            $item->fechaRegistro = date("d-m-Y", strtotime($item->fecha_registrado));
            $item->fechaInicio = date("d-m-Y", strtotime($item->fecha_inicio));
            $item->fechaFin = date("d-m-Y", strtotime($item->fecha_fin));
            $item->precioFormat = '$' . number_format((float)$item->precio, 2, '.', ',');

            return $item;
        });

        return view('backend.admin.historial.membresia.tablahistorialmembresia', compact('arrayHistorial'));
    }



    public function informacionHistorialMembresia(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($info = UsuarioMembresia::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $info];
        }else{
            return ['success' => 2];
        }
    }


    public function editarHistorialMembresia(Request $request)
    {
        $regla = array(
            'id' => 'required',
            'nombre' => 'required',
            'precio' => 'required',
            'fechainicio' => 'required',
            'fechafin' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        // Convertir a objetos Carbon
        $inicio = Carbon::parse($request->fechainicio);
        $fin = Carbon::parse($request->fechafin);

        // Validar que la fecha de inicio no sea mayor que la final
        if ($inicio->gt($fin)) {
            return ['success' => 2, 'mensaje' => 'La fecha de inicio no puede ser mayor que la fecha final'];
        }

        // Calcular los días de duración (incluir el mismo día)
        $duracion = $inicio->diffInDays($fin) + 1;

        UsuarioMembresia::where('id', $request->id)->update([
            'fecha_inicio' => $request->fechainicio,
            'fecha_fin' => $request->fechafin,
            'duracion_dias' => $duracion,
            'precio' => $request->precio,
            'nombre' => $request->nombre
        ]);

        return ['success' => 1];
    }



    // ===== CLIENTES CUMPLEN HOY =======


    public function indexListadoClientesCumpleanero()
    {
        $predeterminado = $this->getExtras();
        Carbon::setLocale('es');
        $fechaActual = Carbon::now('America/El_Salvador')->translatedFormat('F');

        return view('backend.admin.cliente.cumpleaneros.vistaclientescumpleanero', compact('predeterminado', 'fechaActual'));
    }

    public function tablaListadoClientesCumpleanero()
    {
        $tz = 'America/El_Salvador';
        $mesActual = Carbon::now($tz)->month;

        $arrayClientes = Usuario::whereMonth('fecha_nacimiento', $mesActual)
            ->orderBy('fecha_nacimiento', 'asc')
            ->get()
            ->map(function($item) use ($tz) {
                $item->genero = ($item->sexo == 1) ? 'Masculino' : 'Femenino';
                $item->fechaNacimiento = date("d-m-Y", strtotime($item->fecha_nacimiento));

                // Calcular edad
                $item->edad = Carbon::parse($item->fecha_nacimiento)->age;

                // Día del cumpleaños
                $item->diaCumple = Carbon::parse($item->fecha_nacimiento)->format('d');

                return $item;
            });

        return view('backend.admin.cliente.cumpleaneros.tablaclientescumpleanero', compact('arrayClientes'));
    }



}
