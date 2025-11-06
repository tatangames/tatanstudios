<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GymDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('membresia_abono')->truncate();
        DB::table('usuario_membresia')->truncate();
        DB::table('usuarios')->truncate();
        DB::table('membresias')->truncate();
        DB::table('maquinarias_mantenimiento')->truncate();
        DB::table('maquinarias')->truncate();
        DB::table('categoria_maquinaria')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tz = 'America/El_Salvador';
        $faker = \Faker\Factory::create();
        $faker->locale('es_ES');

        // ===========================
        // 1. MEMBRESÍAS BASE
        // ===========================
        $membresias = [
            ['nombre' => 'Mensual', 'precio' => 25, 'duracion_dias' => 30, 'descripcion' => 'Membresía de un mes.'],
            ['nombre' => 'Trimestral', 'precio' => 65, 'duracion_dias' => 90, 'descripcion' => 'Membresía de tres meses.'],
            ['nombre' => 'Anual', 'precio' => 240, 'duracion_dias' => 365, 'descripcion' => 'Membresía de un año.'],
        ];
        DB::table('membresias')->insert($membresias);

        // ===========================
        // 2. CATEGORÍAS DE MAQUINARIA
        // ===========================
        $categorias = ['Cardio', 'Pesas', 'Funcional', 'Accesorios'];
        foreach ($categorias as $cat) {
            DB::table('categoria_maquinaria')->insert(['nombre' => $cat]);
        }

        // ===========================
        // 3. MAQUINARIAS
        // ===========================
        $maquinarias = [];
        for ($i = 0; $i < 8; $i++) {
            $maquinarias[] = [
                'id_maqui_categoria' => rand(1, count($categorias)),
                'fecha' => $faker->dateTimeBetween('-2 years', 'now', $tz)->format('Y-m-d'),
                'nombre' => 'Equipo ' . ($i + 1),
                'precio' => $faker->randomFloat(2, 300, 2500),
                'descripcion' => $faker->sentence(8)
            ];
        }
        DB::table('maquinarias')->insert($maquinarias);

        // ===========================
        // 4. MANTENIMIENTOS
        // ===========================
        $mantenimientos = [];
        for ($i = 1; $i <= count($maquinarias); $i++) {
            $numMant = rand(1, 3);
            for ($j = 0; $j < $numMant; $j++) {
                $mantenimientos[] = [
                    'id_maquinarias' => $i,
                    'fecha' => $faker->dateTimeBetween('-1 year', 'now', $tz)->format('Y-m-d'),
                    'descripcion' => 'Mantenimiento de rutina',
                    'precio' => $faker->randomFloat(2, 25, 250),
                ];
            }
        }
        DB::table('maquinarias_mantenimiento')->insert($mantenimientos);

        // ===========================
        // 5. USUARIOS
        // ===========================
        $usuarios = [];
        for ($i = 1; $i <= 25; $i++) {
            $usuarios[] = [
                'fecha_registrado' => $faker->dateTimeBetween('-1 year', 'now', $tz)->format('Y-m-d'),
                'nombre' => $faker->name(),
                'imagen' => null,
                'correo' => $faker->safeEmail(),
                'telefono' => $faker->phoneNumber(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-45 years', '-18 years')->format('Y-m-d'),
                'sexo' => rand(0, 1),
                'emergencia_nombre' => $faker->firstName(),
                'emergencia_telefono' => $faker->phoneNumber(),
                'condicion_medica' => rand(0, 1) ? null : 'Ninguna',
            ];
        }
        DB::table('usuarios')->insert($usuarios);

        // ===========================
        // 6. USUARIO_MEMBRESIA + ABONOS
        // ===========================
        $usuarioMembresias = [];
        $abonos = [];

        foreach (DB::table('usuarios')->get() as $u) {
            $numMembresias = rand(1, 3);
            for ($i = 0; $i < $numMembresias; $i++) {
                $memb = DB::table('membresias')->inRandomOrder()->first();

                $fechaInicio = Carbon::now($tz)->subMonths(rand(0, 12))->subDays(rand(0, 20));
                $fechaFin = (clone $fechaInicio)->addDays($memb->duracion_dias);
                $fechaRegistrado = (clone $fechaInicio)->subDays(rand(0, 3));

                $isActual = ($i === $numMembresias - 1) ? 1 : 0;
                $solvente = rand(0, 1);

                $umId = DB::table('usuario_membresia')->insertGetId([
                    'id_usuarios'     => $u->id,
                    'id_membresia'    => $memb->id,
                    'fecha_registrado'=> $fechaRegistrado->format('Y-m-d'),
                    'fecha_inicio'    => $fechaInicio->format('Y-m-d'),
                    'fecha_fin'       => $fechaFin->format('Y-m-d'),
                    'nombre'          => $memb->nombre,
                    'precio'          => $memb->precio,
                    'duracion_dias'   => $memb->duracion_dias,
                    'solvente'        => $solvente,
                    'is_actual'       => $isActual,
                ]);

                // Generar entre 1 y 3 abonos por membresía
                $totalAbonos = 0;
                $numAbonos = rand(1, 3);
                for ($j = 0; $j < $numAbonos; $j++) {
                    $monto = $faker->randomFloat(2, 5, $memb->precio / $numAbonos);
                    $fechaPago = $fechaInicio->copy()->addDays(rand(0, $memb->duracion_dias));

                    $abonos[] = [
                        'id_usuario_membresia' => $umId,
                        'fecha_pago' => $fechaPago->format('Y-m-d'),
                        'monto' => $monto,
                    ];
                    $totalAbonos += $monto;
                }

                // Ajustar solvencia
                $nuevoSolvente = ($totalAbonos >= $memb->precio) ? 1 : 0;
                DB::table('usuario_membresia')->where('id', $umId)->update(['solvente' => $nuevoSolvente]);
            }
        }

        DB::table('membresia_abono')->insert($abonos);

        echo "Seeder completado correctamente ✅\n";
    }
}
