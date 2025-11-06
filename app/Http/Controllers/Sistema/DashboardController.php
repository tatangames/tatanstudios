<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Extras;
use App\Models\Maquinaria;
use App\Models\MaquinariaMantenimiento;
use App\Models\MembresiaAbono;
use App\Models\Usuario;
use App\Models\UsuarioMembresia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class DashboardController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    private function getExtras(){
        return Extras::value('tema');
    }

    public function vistaDashboard()
    {
        $predeterminado = $this->getExtras();

        $totalRegistrados = Usuario::count();

        // Total en membresías ganadas (solo abonos; el inicial también lo registras aquí)
        $totalMembresiaFormat = '$' . number_format((float) MembresiaAbono::sum('monto'), 2, '.', ',');

        // Gastos de equipo y mantenimiento
        $totalGastoEquipoFormat = '$' . number_format((float) Maquinaria::sum('precio'), 2, '.', ',');
        $totalGastoEquipoMantenimientoFormat = '$' . number_format((float) MaquinariaMantenimiento::sum('precio'), 2, '.', ',');

        return view('backend.admin.dashboard.vistadashboard', compact(
            'predeterminado',
            'totalRegistrados',
            'totalMembresiaFormat',
            'totalGastoEquipoFormat',
            'totalGastoEquipoMantenimientoFormat'
        ));
    }

    // ============================================================
    // ====================  ENDPOINTS JSON  ======================
    // ============================================================

    /**
     * 1) Ingresos mensuales (suma de abonos por mes)
     */
    public function ingresosMensuales()
    {
        $rows = MembresiaAbono::selectRaw("DATE_FORMAT(fecha_pago, '%Y-%m') as ym, SUM(monto) as total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        return response()->json([
            'labels' => $rows->pluck('ym'),
            'data'   => $rows->pluck('total')->map(fn($v) => (float) $v),
        ]);
    }

    /**
     * 2) Clientes nuevos por mes (fecha_registrado de usuarios)
     */
    public function clientesNuevos()
    {
        $rows = Usuario::selectRaw("DATE_FORMAT(fecha_registrado, '%Y-%m') as ym, COUNT(*) as c")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        return response()->json([
            'labels' => $rows->pluck('ym'),
            'data'   => $rows->pluck('c')->map(fn($v) => (int) $v),
        ]);
    }

    /**
     * 3) Estado de membresías (solo las actuales is_actual=1)
     *    Activas / Por vencer (<=5) / Hoy / Vencidas
     */
    public function estadoMembresias()
    {
        $tz  = 'America/El_Salvador';
        $hoy = Carbon::today($tz);

        $activos = $porVencer = $hoyCount = $vencidos = 0;

        UsuarioMembresia::where('is_actual', 1)->chunk(500, function ($chunk) use (&$activos, &$porVencer, &$hoyCount, &$vencidos, $hoy, $tz) {
            foreach ($chunk as $um) {
                $fin  = Carbon::parse($um->fecha_fin, $tz)->endOfDay();
                $dias = $hoy->diffInDays($fin, false); // negativo si ya venció

                if ($dias < 0)        { $vencidos++; }
                elseif ($dias === 0)  { $hoyCount++; }
                elseif ($dias <= 5)   { $porVencer++; }
                else                  { $activos++; }
            }
        });

        return response()->json([
            'labels' => ['Activas', 'Por vencer', 'Hoy', 'Vencidas'],
            'data'   => [$activos, $porVencer, $hoyCount, $vencidos],
        ]);
    }

    /**
     * 4) Cobrado vs Pendiente (global)
     *    - Cobrado: suma total de abonos
     *    - Pendiente: sumatoria por membresía actual de max(precio - abonos, 0)
     */
    public function cobradoVsPendiente()
    {
        // Todo lo cobrado está en la tabla de abonos
        $cobrado = (float) MembresiaAbono::sum('monto');

        // Pendiente global sumando por membresía actual
        $pendiente = 0.0;
        UsuarioMembresia::where('is_actual', 1)->chunk(500, function ($chunk) use (&$pendiente) {
            foreach ($chunk as $um) {
                $abonos = (float) MembresiaAbono::where('id_usuario_membresia', $um->id)->sum('monto');
                $deuda  = max(((float) $um->precio) - $abonos, 0);
                $pendiente += $deuda;
            }
        });

        return response()->json([
            'labels' => ['Cobrado', 'Pendiente'],
            'data'   => [round($cobrado, 2), round($pendiente, 2)],
        ]);
    }

    /**
     * 5) Ingresos por tipo de membresía (plan)
     *    Agrupa por el campo denormalizado 'nombre' en usuario_membresia
     */
    public function ingresosPorPlan()
    {
        $abTable = (new MembresiaAbono)->getTable();         // 'membresia_abono'
        $umTable = (new UsuarioMembresia)->getTable();       // 'usuario_membresia'

        $rows = DB::table($abTable . ' as ma')
            ->join($umTable . ' as um', 'um.id', '=', 'ma.id_usuario_membresia')
            ->selectRaw('um.nombre as plan, SUM(ma.monto) as total')
            ->groupBy('plan')
            ->orderBy('plan')
            ->get();

        return response()->json([
            'labels' => $rows->pluck('plan'),
            'data'   => $rows->pluck('total')->map(fn($v) => (float) $v),
        ]);
    }

    /**
     * 6) Membresías vencidas por antigüedad (rangos de días)
     */
    public function vencidasAntiguedad()
    {
        $tz  = 'America/El_Salvador';
        $hoy = Carbon::today($tz);

        $buckets = [
            '0–7'   => 0,
            '8–30'  => 0,
            '31–60' => 0,
            '60+'   => 0,
        ];

        UsuarioMembresia::where('is_actual', 1)->chunk(500, function ($chunk) use (&$buckets, $hoy, $tz) {
            foreach ($chunk as $um) {
                $fin  = Carbon::parse($um->fecha_fin, $tz)->endOfDay();
                $dias = $hoy->diffInDays($fin, false);
                if ($dias < 0) {
                    $d = abs($dias);
                    if ($d <= 7)        { $buckets['0–7']++; }
                    elseif ($d <= 30)   { $buckets['8–30']++; }
                    elseif ($d <= 60)   { $buckets['31–60']++; }
                    else                { $buckets['60+']++; }
                }
            }
        });

        return response()->json([
            'labels' => array_keys($buckets),
            'data'   => array_values($buckets),
        ]);
    }

    /**
     * 7) Top 10 clientes por deuda (solo actuales)
     */
    public function topDeudores()
    {
        $umTable = (new UsuarioMembresia)->getTable();   // 'usuario_membresia'
        $abTable = (new MembresiaAbono)->getTable();     // 'membresia_abono'
        $usTable = (new Usuario)->getTable();            // 'usuarios'

        // Subconsulta: total de abonos por membresía
        $sub = DB::table($abTable)
            ->select('id_usuario_membresia', DB::raw('SUM(monto) as pagado'))
            ->groupBy('id_usuario_membresia');

        // Unimos: membresía actual + usuario + abonos
        $rows = DB::table($umTable . ' as um')
            ->leftJoinSub($sub, 'ab', 'ab.id_usuario_membresia', '=', 'um.id')
            ->leftJoin($usTable . ' as u', 'u.id', '=', 'um.id_usuarios')
            ->where('um.is_actual', 1)
            ->selectRaw("
            um.id,
            um.precio,
            COALESCE(u.nombre, CONCAT('Cliente #', um.id_usuarios)) as cliente,
            COALESCE(ab.pagado, 0) as pagado
        ")
            ->get()
            ->map(function ($r) {
                $deuda = max(((float) $r->precio) - (float) $r->pagado, 0);
                return ['cliente' => $r->cliente, 'deuda' => round($deuda, 2)];
            })
            ->sortByDesc('deuda')
            ->take(10)
            ->values();

        return response()->json([
            'labels' => $rows->pluck('cliente'),
            'data'   => $rows->pluck('deuda'),
        ]);
    }


    public function costoBeneficio()
    {
        $ingresos          = (float) \App\Models\MembresiaAbono::sum('monto');
        $gastoMaquinaria   = (float) \App\Models\Maquinaria::sum('precio');
        $gastoMantenimiento= (float) \App\Models\MaquinariaMantenimiento::sum('precio');
        $gastosTotales     = $gastoMaquinaria + $gastoMantenimiento;
        $neto              = $ingresos - $gastosTotales;

        return response()->json([
            'labels' => ['Gastos (Maquinaria + Mantenimiento)', 'Ingresos (Membresías)'],
            'data'   => [round($gastosTotales, 2), round($ingresos, 2)],
            'extra'  => [
                'gasto_maquinaria'    => round($gastoMaquinaria, 2),
                'gasto_mantenimiento' => round($gastoMantenimiento, 2),
                'gastos_totales'      => round($gastosTotales, 2),
                'ingresos'            => round($ingresos, 2),
                'neto'                => round($neto, 2),
            ],
        ]);
    }




}
