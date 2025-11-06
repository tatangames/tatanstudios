<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sistema\LoginController;
use App\Http\Controllers\Sistema\ControlController;
use App\Http\Controllers\Sistema\RolesController;
use App\Http\Controllers\Sistema\PerfilController;
use App\Http\Controllers\Sistema\PermisoController;
use App\Http\Controllers\Sistema\DashboardController;
use App\Http\Controllers\Sistema\ConfiguracionController;
use App\Http\Controllers\Sistema\ClienteController;
use App\Http\Controllers\Sistema\BackupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [LoginController::class,'vistaLoginForm'])->name('login.admin');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

// --- CONTROL WEB ---
Route::get('/panel', [ControlController::class,'indexRedireccionamiento'])->name('admin.panel');

// --- ROLES ---
Route::get('/admin/roles/index', [RolesController::class,'index'])->name('admin.roles.index');
Route::get('/admin/roles/tabla', [RolesController::class,'tablaRoles']);
Route::get('/admin/roles/lista/permisos/{id}', [RolesController::class,'vistaPermisos']);
Route::get('/admin/roles/permisos/tabla/{id}', [RolesController::class,'tablaRolesPermisos']);
Route::post('/admin/roles/permiso/borrar', [RolesController::class, 'borrarPermiso']);
Route::post('/admin/roles/permiso/agregar', [RolesController::class, 'agregarPermiso']);
Route::get('/admin/roles/permisos/lista', [RolesController::class,'listaTodosPermisos']);
Route::get('/admin/roles/permisos-todos/tabla', [RolesController::class,'tablaTodosPermisos']);
Route::post('/admin/roles/borrar-global', [RolesController::class, 'borrarRolGlobal']);

// --- PERMISOS ---
Route::get('/admin/permisos/index', [PermisoController::class,'index'])->name('admin.permisos.index');
Route::get('/admin/permisos/tabla', [PermisoController::class,'tablaUsuarios']);
Route::post('/admin/permisos/nuevo-usuario', [PermisoController::class, 'nuevoUsuario']);
Route::post('/admin/permisos/info-usuario', [PermisoController::class, 'infoUsuario']);
Route::post('/admin/permisos/editar-usuario', [PermisoController::class, 'editarUsuario']);
Route::post('/admin/permisos/nuevo-rol', [PermisoController::class, 'nuevoRol']);
Route::post('/admin/permisos/extra-nuevo', [PermisoController::class, 'nuevoPermisoExtra']);
Route::post('/admin/permisos/extra-borrar', [PermisoController::class, 'borrarPermisoGlobal']);

// --- PERFIL ---
Route::get('/admin/editar-perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
Route::post('/admin/editar-perfil/actualizar', [PerfilController::class, 'editarUsuario']);

Route::get('sin-permisos', [ControlController::class,'indexSinPermiso'])->name('no.permisos.index');

// actualizar Tema
Route::post('/admin/actualizar/tema', [ControlController::class, 'actualizarTema'])->name('admin.tema.update');



// Página
Route::get('/admin/dashboard', [DashboardController::class,'vistaDashboard'])
    ->name('admin.dashboard');

// JSON (con prefijo)
Route::prefix('admin/dashboard')->name('admin.dashboard.')->group(function () {
    Route::get('/ingresos-mensuales',   [DashboardController::class, 'ingresosMensuales'])->name('ingresos');
    Route::get('/clientes-nuevos',      [DashboardController::class, 'clientesNuevos'])->name('clientes');
    Route::get('/estado-membresias',    [DashboardController::class, 'estadoMembresias'])->name('estado');
    Route::get('/cobrado-vs-pendiente', [DashboardController::class, 'cobradoVsPendiente'])->name('cvp');
    Route::get('/ingresos-por-plan',    [DashboardController::class, 'ingresosPorPlan'])->name('plan');
    Route::get('/vencidas-antiguedad',  [DashboardController::class, 'vencidasAntiguedad'])->name('vencidas');
    Route::get('/top-deudores',         [DashboardController::class, 'topDeudores'])->name('deudores');
    Route::get('/costo-beneficio', [DashboardController::class, 'costoBeneficio'])->name('admin.dashboard.costo_beneficio');

});








// === MEMBRESIAS ===
Route::get('/admin/membresias/index', [ConfiguracionController::class,'indexMembresia'])->name('admin.membresia.index');
Route::get('/admin/membresias/tabla', [ConfiguracionController::class,'tablaMembresia']);
Route::post('/admin/membresias/nuevo', [ConfiguracionController::class,'nuevoMembresia']);
Route::post('/admin/membresias/informacion', [ConfiguracionController::class,'informacionMembresia']);
Route::post('/admin/membresias/editar', [ConfiguracionController::class,'editarMembresia']);

// === REGISTRO DE CLIENTE ===
Route::get('/admin/cliente/nuevo/index', [ClienteController::class,'vistaNuevoCliente'])->name('admin.nuevo.cliente.index');
Route::post('/admin/cliente/nuevo', [ClienteController::class,'registrarCliente']);

// === VISTA DE CLIENTES ===
Route::get('/admin/cliente/listado/index', [ClienteController::class,'indexListadoClientes'])->name('admin.listado.clientes.index');
Route::get('/admin/cliente/listado/tabla/{filtro?}', [ClienteController::class,'tablaListadoClientes']);
Route::post('/admin/cliente/informacion', [ClienteController::class,'informacionCliente']);
Route::post('/admin/cliente/editar', [ClienteController::class,'editarCliente']);

// === CLIENTES QUE TIENEN DEUDA DE MEMBRESIA ===
Route::get('/admin/cliente-deuda/listado/index', [ClienteController::class,'indexListadoClientesConDeuda'])->name('admin.listado.clientes.deuda.index');
Route::get('/admin/cliente-deuda/listado/tabla', [ClienteController::class,'tablaListadoClientesConDeuda']);
Route::post('/admin/cliente-deuda/informacion', [ClienteController::class,'informacionClienteConDeuda']);
Route::post('/admin/cliente-deuda/abonar', [ClienteController::class,'abonarClienteConDeuda']);

// === REGISTRO NUEVA MEMBRESIA ====
Route::post('/admin/cliente/nueva/membresia', [ClienteController::class,'nuevaMembresiaCliente']);


// === VISTA DE CLIENTES VENCIDOS ===
Route::get('/admin/cliente-vencidos/listado/index', [ClienteController::class,'indexListadoClientesVencidos'])->name('admin.listado.clientes.vencidos.index');
Route::get('/admin/cliente-vencidos/listado/tabla', [ClienteController::class,'tablaListadoClientesVencidos']);


// === CATEGORIA DE MAQUINARIA ===
Route::get('/admin/categorias/maquinaria/index', [ConfiguracionController::class,'indexCategoriaMaquinaria'])->name('admin.categoria.maquinaria.index');
Route::get('/admin/categorias/maquinaria/tabla', [ConfiguracionController::class,'tablaCategoriaMaquinaria']);
Route::post('/admin/categorias/maquinaria/nuevo', [ConfiguracionController::class,'nuevoCategoriaMaquinaria']);
Route::post('/admin/categorias/maquinaria/informacion', [ConfiguracionController::class,'informacionCategoriaMaquinaria']);
Route::post('/admin/categorias/maquinaria/editar', [ConfiguracionController::class,'editarCategoriaMaquinaria']);



// === MAQUINARIA ===
Route::get('/admin/maquinaria/index', [ConfiguracionController::class,'indexMaquinaria'])->name('admin.maquinaria.index');
Route::get('/admin/maquinaria/tabla', [ConfiguracionController::class,'tablaMaquinaria']);
Route::post('/admin/maquinaria/nuevo', [ConfiguracionController::class,'nuevoMaquinaria']);
Route::post('/admin/maquinaria/informacion', [ConfiguracionController::class,'informacionMaquinaria']);
Route::post('/admin/maquinaria/editar', [ConfiguracionController::class,'editarMaquinaria']);
Route::post('/admin/maquinaria/borrar', [ConfiguracionController::class,'borrarMaquinaria']);


// === HISTORIAL MEMBRESIAS ===
Route::get('/admin/historial/membresia/index/{idcliente}', [ClienteController::class,'indexHistorialMembresia']);
Route::get('/admin/historial/membresia/tabla/{idcliente}', [ClienteController::class,'tablaHistorialMembresia']);
Route::post('/admin/historial/membresia/informacion', [ClienteController::class,'informacionHistorialMembresia']);
Route::post('/admin/historial/membresia/actualizar', [ClienteController::class,'editarHistorialMembresia']);

// ==== REGISTRO DE MANTENIMIENTO DE MAQUINARIA ====
Route::get('/admin/historial/mantenimiento/index/{idequipo}', [ConfiguracionController::class,'indexHistorialMantenimientos']);
Route::get('/admin/historial/mantenimiento/tabla/{idequipo}', [ConfiguracionController::class,'tablaHistorialMantenimientos']);
Route::post('/admin/historial/mantenimiento/nuevo', [ConfiguracionController::class,'nuevoMaquinariaMantenimientos']);
Route::post('/admin/historial/mantenimiento/borrar', [ConfiguracionController::class,'borrarHistorialMantenimientos']);


// ==== CUMPLEANERO HOY ====
Route::get('/admin/cliente/listado/cumplenhoy/index', [ClienteController::class,'indexListadoClientesCumpleanero'])
    ->name('admin.listado.clientes.cumplenhoy.index');
Route::get('/admin/cliente/listado/cumplenhoy/tabla', [ClienteController::class,'tablaListadoClientesCumpleanero']);


// ===== BACKUP =====
Route::get('/admin/backup/index', [BackupController::class,'vistaBackup'])->name('admin.backup.index');

Route::post('/admin/backup/db', [BackupController::class, 'dump'])
    ->name('admin.backup.db');

