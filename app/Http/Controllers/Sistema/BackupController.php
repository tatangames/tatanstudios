<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Extras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    private function getExtras(){
        return Extras::value('tema');
    }

    public function vistaBackup()
    {
        $predeterminado = $this->getExtras();

        return view('backend.admin.backup.vistabackup', compact('predeterminado'));
    }

    public function dump(Request $request)
    {
        $conn   = DB::connection()->getConfig();
        $db     = $conn['database'];
        $user   = $conn['username'];
        $pass   = $conn['password'] ?? '';
        $host   = $conn['host'] ?? '127.0.0.1';
        $port   = $conn['port'] ?? '3306';

        // Usa el DUMP_PATH del .env; si no existe, lanza un mensaje claro
        $dump = env('DUMP_PATH');
        if (!$dump || !file_exists($dump)) {
            return response()->json([
                'success' => 0,
                'message' => 'No se encontró el binario de mysqldump en DUMP_PATH del .env',
                'error'   => $dump ?: '(vacío)',
            ], 500);
        }

        // Carpeta destino
        $folder = storage_path('app/backups');
        if (!is_dir($folder)) {
            @mkdir($folder, 0775, true);
        }

        // Nombre archivo
        $filename = sprintf('%s_%s.sql', $db, now('America/El_Salvador')->format('Ymd_His'));
        $filepath = $folder . DIRECTORY_SEPARATOR . $filename;

        // Armar argumentos (sin pasar --password si está vacío)
        $args = [
            $dump,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $user,

            // Opcionales y recomendados en MySQL 8
            '--column-statistics=0',
            '--set-gtid-purged=OFF',

            // Buenas prácticas de consistencia/velocidad
            '--single-transaction',
            '--quick',
            '--routines',
            '--events',

            '--databases', $db,
            '--result-file=' . $filepath,
            '--skip-comments',
        ];
        if ($pass !== '') {
            $args[] = '--password=' . $pass;
        }

        $process = new Process($args);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            $error = $process->getErrorOutput() ?: $process->getOutput();
            return response()->json([
                'success' => 0,
                'message' => 'Falló el backup',
                'error'   => Str::limit($error, 800),
            ], 500);
        }

        // Descarga y borra el archivo luego de enviarlo
        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/sql'
        ])->deleteFileAfterSend(true);
    }
}
