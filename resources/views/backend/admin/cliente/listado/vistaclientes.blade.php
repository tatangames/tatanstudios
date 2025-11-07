@extends('adminlte::page')

@section('title', 'Listado Clientes')

@section('meta_tags')
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ultra.jpg') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/ultra.jpg') }}">
@endsection

@section('content_header')

@stop
{{-- Activa plugins que necesitas --}}
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Sweetalert2', true)

@section('content_top_nav_right')
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />

    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" title="Tema">
            <i id="theme-icon" class="fas fa-sun"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right p-0" style="min-width: 180px">
            <a class="dropdown-item d-flex align-items-center" href="#" data-theme="dark">
                <i class="far fa-moon mr-2"></i> Dark
            </a>
            <a class="dropdown-item d-flex align-items-center" href="#" data-theme="light">
                <i class="far fa-sun mr-2"></i> Light
            </a>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a href="#" class="nav-link" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-cogs"></i>
            <span class="d-none d-md-inline">{{ Auth::guard('admin')->user()->nombre ?? 'Usuario' }}</span>
        </a>

        <div class="dropdown-menu dropdown-menu-right">
            <a href="{{ route('admin.perfil') }}" class="dropdown-item">
                <i class="fas fa-user mr-2"></i> Editar Perfil
            </a>

            <div class="dropdown-divider"></div>

            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item">
                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </li>
@endsection

@section('content')

    <div id="divcontenedor">

        <section class="content">
            <div class="container-fluid">
                <div class="card card-gray-dark">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h3 class="card-title mb-2 mb-sm-0">Lista de Clientes</h3>

                        <div class="card-tools w-100 w-sm-auto">
                            <div class="form-inline justify-content-sm-end">
                                <label class="mb-0 mr-2">Vencen en:</label>
                                <select id="select-filtro" class="form-control form-control-sm" style="min-width: 140px">
                                    <option value="0" selected>Mostrar Todos</option>
                                    <option value="1">1 días</option>
                                    <option value="2">2 días</option>
                                    <option value="3">3 días</option>
                                    <option value="4">4 días</option>
                                    <option value="5">5 días</option>
                                    <option value="6">6 días</option>
                                    <option value="7">7 días</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div id="tablaDatatable"></div>
                    </div>
                </div>
            </div>
        </section>


        <div class="modal fade" id="modalInfo">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Información</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <!-- ID CLIENTE -->
                                        <div class="form-group">
                                            <input type="hidden" id="id-editarbloque">
                                        </div>

                                        <div class="d-flex flex-column align-items-stretch gap-2">

                                            <button type="button"
                                                    style="font-weight: bold; background-color: #007bff; color: white !important;"
                                                    class="btn btn-primary btn-sm mb-2 d-flex align-items-center justify-content-center"
                                                    onclick="verInformacion()">
                                                <i class="fas fa-user mr-2"></i> Información Cliente
                                            </button>

                                            <br>

                                            <button type="button"
                                                    style="font-weight: bold; background-color: #28a745; color: white !important;"
                                                    class="btn btn-success btn-sm d-flex align-items-center justify-content-center"
                                                    onclick="informacionParaMembresia()">
                                                <i class="fas fa-id-card mr-2"></i> Nueva Membresía
                                            </button>
                                            <br>
                                            <button type="button"
                                                    style="font-weight: bold; background-color: #d8b916; color: white !important;"
                                                    class="btn btn-warning btn-sm d-flex align-items-center justify-content-center"
                                                    onclick="infoMembresiaPanel()">
                                                <i class="fas fa-id-card mr-2"></i> Modificar Membresía
                                            </button>


                                        </div>


                                    </div>
                                </div>
                            </div>

                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

                    </div>
                </div>
            </div>
        </div>





        <div class="modal fade" id="modalEditar">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Editar Registro</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="form-group">
                                            <input type="hidden" id="id-editar">
                                        </div>

                                        <div class="row g-3">
                                            <!-- BLOQUE IZQUIERDO -->
                                            <div class="col-md-6">
                                                <div class="card card-primary">
                                                    <div class="card-body py-3">
                                                        <form id="formulario-editar">
                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Nombre <span style="color: red">*</span></label>
                                                                <input type="text" maxlength="100" autocomplete="off" class="form-control form-control-sm" id="nombre-editar" placeholder="Nombre">
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Correo</label>
                                                                <input type="text" maxlength="100" autocomplete="off" class="form-control form-control-sm" id="correo-editar" placeholder="Correo electrónico">
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Teléfono</label>
                                                                <input type="text" maxlength="25" autocomplete="off" class="form-control form-control-sm" id="telefono-editar" placeholder="Teléfono">
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label class="mb-1">Fecha de Nacimiento <span style="color: red">*</span></label>
                                                                <input type="date" class="form-control form-control-sm" id="fechanacimiento-editar" placeholder="Fecha de Nacimiento">
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label class="mb-1">Sexo <span style="color: red">*</span></label>
                                                                <select class="form-control form-control-sm" id="sexo-editar">
                                                                    <option value="1">Masculino</option>
                                                                    <option value="0">Femenino</option>
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- BLOQUE DERECHO -->
                                            <div class="col-md-6">
                                                <div class="card card-secondary">
                                                    <div class="card-body py-3">
                                                        <h6 class="mb-3" style="font-weight: bold">En caso de Emergencia</h6>
                                                        <form id="formulario2-editar">
                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Nombre</label>
                                                                <input type="text" maxlength="100" autocomplete="off" class="form-control form-control-sm" id="nombreemergencia-editar" placeholder="Nombre">
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Teléfono</label>
                                                                <input type="text" maxlength="25" autocomplete="off" class="form-control form-control-sm" id="telefonoemergencia-editar" placeholder="Teléfono">
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Condición Médica</label>
                                                                <input type="text" maxlength="800" autocomplete="off" class="form-control form-control-sm" id="condicionemergencia-editar" placeholder="Condición">
                                                            </div>


                                                            <!-- Imagen de perfil -->
                                                            <div class="text-center mb-3" style="margin-top: 25px">
                                                                <img id="imagen-perfil"
                                                                     src="{{ asset('images/perfil.png') }}"
                                                                     alt="Foto de perfil"
                                                                     style="width: 150px; height: 150px; object-fit: contain; border: 2px solid #ccc; cursor: pointer;"
                                                                     data-toggle="modal"
                                                                     data-target="#modalVerImagen">
                                                            </div>


                                                            <div class="col-md-10" style="margin-top: 15px">
                                                                <input type="file" style="color:#191818" id="imagen-nuevo" accept="image/jpeg, image/jpg, image/png"/>
                                                            </div>


                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" style="font-weight: bold; background-color: #28a745; color: white !important;"
                                class="btn btn-success btn-sm" id="btn-guardar" onclick="editar()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>



        <!-- Modal para ver imagen -->
        <div class="modal fade" id="modalVerImagen" tabindex="-1" role="dialog" aria-labelledby="modalVerImagenLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content bg-dark">
                    <div class="modal-body text-center p-2">
                        <img id="imagen-modal" src="{{ asset('images/perfil.png') }}" alt="Vista previa"
                             class="img-fluid rounded" style="max-height: 85vh; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>





        <div class="modal fade" id="modalMembresia">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Nueva Membresia</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <form id="form-membresia">
                                        <input type="hidden" id="id-editarmembresia">

                                        <div class="row g-3">
                                            <!-- BLOQUE IZQUIERDO -->
                                            <div class="col-md-12">
                                                <div class="card card-primary">
                                                    <div class="card-body py-3">
                                                        <form id="formulario-membresia">
                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Cliente</label>
                                                                <input type="text" id="cliente-nombre-membresia" class="form-control form-control-sm" readonly>
                                                            </div>

                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Teléfono</label>
                                                                <input type="text" id="cliente-telefono-membresia" class="form-control form-control-sm" readonly>
                                                            </div>

                                                            <hr>

                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Fecha Inicio</label>
                                                                <input type="date" id="fecha-nuevo" class="form-control form-control-sm">
                                                            </div>

                                                            <div class="form-group mb-3" style="max-width: 100%">
                                                                <label class="mb-1">Membresía <span style="color: red">*</span></label>
                                                                <select class="form-control form-control-sm" id="membresia-nuevo">
                                                                    <option value="">Seleccionar Opción</option>
                                                                    @foreach($arrayMembresias as $item)
                                                                        <option value="{{$item->id}}">{{ $item->nombreCompleto }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>


                                                            <div class="form-group mb-2" style="max-width: 30%">
                                                                <label class="mb-1">En caso de Abono</label>
                                                                <input type="number" autocomplete="off" class="form-control form-control-sm" id="abono-nuevo" placeholder="Abono">
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" style="font-weight: bold; background-color: #28a745; color: white !important;"
                                class="btn btn-success btn-sm" id="btn-registrar" onclick="registrarMembresia()">Registrar</button>
                    </div>
                </div>
            </div>
        </div>





        @stop



@section('js')
            <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

        <script>

            document.getElementById('imagen-perfil').addEventListener('click', function () {
                const src = this.src;
                document.getElementById('imagen-modal').src = src;
            });

        </script>


            <script>
                let __lastFocused = null;

                function safeBlur() {
                    // Guarda el elemento con foco y quita el foco para evitar el warning
                    __lastFocused = document.activeElement;
                    if (__lastFocused && typeof __lastFocused.blur === 'function') {
                        __lastFocused.blur();
                    }
                }

                // Loader compatible con SweetAlert2 v8
                function openLoading () {
                    safeBlur(); // 👈 clave para evitar el warning

                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Por favor espere un momento.',
                        allowOutsideClick: false,
                        onBeforeOpen: () => { // v8
                            Swal.showLoading();
                        }
                    });
                }

                function closeLoading () {
                    Swal.close();
                    // Opcional: devolver el foco donde estaba
                    if (__lastFocused && typeof __lastFocused.focus === 'function') {
                        try { __lastFocused.focus(); } catch(e) {}
                    }
                }

                // =========================
                // Plugin de orden para DD-MM-YYYY (date-eu)
                // =========================
                (function registerDateEuSortOnce(){
                    if (window.__dateEuSortRegistered) return;
                    window.__dateEuSortRegistered = true;

                    // Detección del tipo date-eu en celdas con formato DD-MM-YYYY (permite espacios)
                    $.fn.dataTable.ext.type.detect.push(function (d) {
                        if (typeof d !== 'string') return null;
                        d = d.trim();
                        // Acepta vacío o guion como nulos
                        if (d === '' || d === '—' || d === '-') return 'date-eu';
                        // Coincide DD-MM-YYYY
                        return /^\d{2}-\d{2}-\d{4}$/.test(d) ? 'date-eu' : null;
                    });

                    // Conversor a número YYYYMMDD para ordenar
                    $.extend($.fn.dataTable.ext.type.order, {
                        'date-eu-pre': function (d) {
                            if (typeof d !== 'string') return 0;
                            d = d.trim();
                            if (d === '' || d === '—' || d === '-') return 0;
                            // esperado DD-MM-YYYY
                            var parts = d.split('-');
                            if (parts.length !== 3) return 0;
                            var dd = parts[0], mm = parts[1], yy = parts[2];
                            // Construye número YYYYMMDD (con padding ya presente)
                            var num = yy + mm + dd;
                            // Evita NaN si hay algo raro
                            var n = parseInt(num, 10);
                            return isNaN(n) ? 0 : n;
                        }
                    });
                })();

                $(function () {
                    const baseRuta = "{{ url('/admin/cliente/listado/tabla') }}";

                    function initDataTable() {
                        if ($.fn.DataTable.isDataTable('#tabla')) {
                            $('#tabla').DataTable().destroy();
                        }

                        $('#tabla').DataTable({
                            // ⚠️ Ajusta el índice de columna si tu fecha no está en la primera columna
                            order: [[0, 'desc']],
                            columnDefs: [
                                // Fuerza el tipo en la columna 0 con formato DD-MM-YYYY
                                { targets: 0, type: 'date-eu' }
                            ],
                            paging: true,
                            lengthChange: true,
                            searching: true,
                            ordering: true,
                            info: true,
                            autoWidth: false,
                            responsive: true,
                            pagingType: "full_numbers",
                            lengthMenu: [[10,25,50,100,150,-1],[10,25,50,100,150,"Todo"]],
                            language: {
                                sProcessing:"Procesando...",
                                sLengthMenu:"Mostrar _MENU_ registros",
                                sZeroRecords:"No se encontraron resultados",
                                sEmptyTable:"Ningún dato disponible en esta tabla",
                                sInfo:"Mostrando _START_ a _END_ de _TOTAL_ registros",
                                sInfoEmpty:"Mostrando 0 a 0 de 0 registros",
                                sInfoFiltered:"(filtrado de _MAX_ registros)",
                                sSearch:"Buscar:",
                                oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
                                oAria:{ sSortAscending:": Orden ascendente", sSortDescending:": Orden descendente" }
                            },
                            dom:"<'row align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-right'f>>tr<'row align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
                        });

                        $('#tabla_length select').addClass('form-control form-control-sm');
                        $('#tabla_filter input').addClass('form-control form-control-sm').css('display','inline-block');
                    }

                    function cargarTabla(filtro = 0) {
                        const rutaFinal = `${baseRuta}/${filtro}`;
                        openLoading();

                        const fuse = setTimeout(() => closeLoading(), 10000); // respaldo

                        $.ajax({ url: rutaFinal, method: 'GET', cache: false })
                            .done(function (html) {
                                $('#tablaDatatable').html(html);
                                try { initDataTable(); }
                                catch(e){
                                    console.error(e);
                                    if (typeof toastr!=='undefined') toastr.error('Error al inicializar la tabla.');
                                }
                            })
                            .fail(function (xhr) {
                                console.error('Error AJAX:', xhr.status, xhr.statusText);
                                if (typeof toastr!=='undefined') toastr.error('No se pudo cargar la tabla.');
                                $('#tablaDatatable').html('<div class="alert alert-danger mb-0">Error al cargar la tabla.</div>');
                            })
                            .always(function () {
                                clearTimeout(fuse);
                                closeLoading(); // ✅ siempre cierra y devuelve el foco
                            });
                    }

                    // Primera carga
                    cargarTabla();

                    // Recargar con filtro
                    window.recargar = function () {
                        // (opcional) blur inmediato por si el usuario está en el select
                        safeBlur();
                        const filtro = document.getElementById('select-filtro').value;
                        cargarTabla(filtro);
                    };

                    $('#select-filtro').on('change', function(){
                        recargar();
                    });
                });
            </script>







            <script>


        function registrarMembresia(){
            var id = document.getElementById('id-editarmembresia').value;
            var membresia = document.getElementById('membresia-nuevo').value; // requerido
            var abono = document.getElementById('abono-nuevo').value;
            var fecha = document.getElementById('fecha-nuevo').value;

            if(fecha === ''){
                toastr.error('Fecha es requerido');
                return;
            }

            if(membresia === ''){
                toastr.error('Membresia es requerido');
                return;
            }

            var reglaNumeroDiesDecimal = /^([0-9]+\.?[0-9]{0,10})$/;

            if(abono === ''){
                // No hacer nada
            }else{
                if(!abono.match(reglaNumeroDiesDecimal)) {
                    toastr.error('Abono debe ser número Decimal');
                    return;
                }

                if(abono < 0){
                    toastr.error('Abono no debe ser negativo');
                    return;
                }

                if(abono > 9000000){
                    toastr.error('Abono debe ser máximo 9 millones');
                    return;
                }
            }

            const btnGuardar = document.getElementById('btn-registrar');
            // Desactivar botón al iniciar
            btnGuardar.disabled = true;
            btnGuardar.textContent = 'Guardando...';

            openLoading();
            var formData = new FormData();
            formData.append('id', id);
            formData.append('membresia', membresia);
            formData.append('fecha', fecha);
            formData.append('abono', abono);

            axios.post('/admin/cliente/nueva/membresia', formData)
                .then((response) => {
                    closeLoading(); // si esto hace Swal.close(), debe ejecutarse ANTES del nuevo Swal

                    if (response.data.success === 1) {
                        const info = response.data;

                        // 1) Mostrar alert
                        return Swal.fire({
                            icon: 'success',
                            title: '¡Membresía registrada!',
                            html: `
          <div style="text-align:left">
            <p><b>Fecha de inicio:</b> ${info.fecha_inicio}</p>
            <p><b>Fecha de vencimiento:</b> ${info.fecha_fin}</p>
            <p><b>Pendiente:</b> $${info.pendiente}</p>
            <p><b>Estado:</b> ${info.solvente ? '<span class="text-success">Solvente</span>' : '<span class="text-warning">Pendiente de pago</span>'}</p>
          </div>
        `,
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3085d6',
                            allowOutsideClick: false,
                        }).then(() => {
                            // 2) Al cerrar el alert, escondemos el modal y recargamos
                            $('#modalMembresia').one('hidden.bs.modal', function () {
                                // Si recargar() destruye/reinicia DataTable o hace location.reload(),
                                // esto evita que mate el Swal antes de tiempo.
                                recargar();
                            });
                            $('#modalMembresia').modal('hide');
                        });

                    } else {
                        toastr.error('Error al registrar');
                    }
                })
                .catch(() => {
                    toastr.error('Error al registrar');
                    closeLoading();
                })
                .finally(() => {
                    resetButton();
                });



            // Función interna para restaurar el botón
            function resetButton() {
                btnGuardar.disabled = false;
                btnGuardar.textContent = 'Guardar';
            }
        }


        function informacionParaMembresia(){
            var id = document.getElementById('id-editarbloque').value;

            $('#modalInfo').modal('hide');

            openLoading();
            document.getElementById("form-membresia").reset();

            axios.post('/admin/cliente/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalMembresia').modal('show');
                        $('#id-editarmembresia').val(response.data.info.id);

                        $('#cliente-nombre-membresia').val(response.data.info.nombre);
                        $('#cliente-telefono-membresia').val(response.data.info.telefono);



                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }


        function abrirModalInfo(id){
            $('#modalInfo').modal('show');
            $('#id-editarbloque').val(id); // ID CLIENTE
        }




        function verInformacion(){
            // Viene del bloque de opciones
            var id = document.getElementById('id-editarbloque').value;

            $('#modalInfo').modal('hide');

            openLoading();
            document.getElementById("formulario-editar").reset();
            document.getElementById("formulario2-editar").reset();

            axios.post('/admin/cliente/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(response.data.info.id);

                        $('#nombre-editar').val(response.data.info.nombre);

                        $('#correo-editar').val(response.data.info.correo);
                        $('#telefono-editar').val(response.data.info.telefono);
                        $('#fechanacimiento-editar').val(response.data.info.fecha_nacimiento);

                        document.getElementById('sexo-editar').value = response.data.info.sexo == 1 ? '1' : '0';

                        $('#nombreemergencia-editar').val(response.data.info.emergencia_nombre);
                        $('#telefonoemergencia-editar').val(response.data.info.emergencia_telefono);
                        $('#condicionemergencia-editar').val(response.data.info.condicion_medica);

                        const imagenPerfil = document.getElementById('imagen-perfil');

                        if (response.data.info.imagen) {
                            imagenPerfil.src = `/storage/archivos/${response.data.info.imagen}`;
                        } else {
                            imagenPerfil.src = `/images/perfil.png`;
                        }

                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }

        function editar(){
            var id = document.getElementById('id-editar').value;
            var nombre = document.getElementById('nombre-editar').value; // requerido
            var correo = document.getElementById('correo-editar').value;
            var telefono = document.getElementById('telefono-editar').value;
            var fechanac = document.getElementById('fechanacimiento-editar').value; // requerido
            var sexo = document.getElementById('sexo-editar').value; // requerido

            var nombreEmergencia = document.getElementById('nombreemergencia-editar').value;
            var telefonoEmergencia = document.getElementById('telefonoemergencia-editar').value;
            var condicionEmergencia = document.getElementById('condicionemergencia-editar').value;

            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }

            if(sexo === ''){
                toastr.error('Seleccionar Sexo del Cliente');
                return;
            }

            // IMAGEN
            var imagen = document.getElementById('imagen-nuevo');

            if(imagen.files && imagen.files[0]){ // si trae imagen
                if (!imagen.files[0].type.match('image/jpeg|image/jpg|image/png')){
                    toastr.error('Formato de imagen permitido: .png .jpg .jpeg');
                    return;
                }
            }


            const btnGuardar = document.getElementById('btn-guardar');
            // Desactivar botón al iniciar
            btnGuardar.disabled = true;
            btnGuardar.textContent = 'Guardando...';

            openLoading();
            var formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nombre);
            formData.append('correo', correo);
            formData.append('telefono', telefono);
            formData.append('fechanac', fechanac);
            formData.append('sexo', sexo);

            formData.append('nombreEmergencia', nombreEmergencia);
            formData.append('telefonoEmergencia', telefonoEmergencia);
            formData.append('condicionEmergencia', condicionEmergencia);
            formData.append('imagen', imagen.files[0]);

            axios.post('/admin/cliente/editar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Actualizado correctamente');
                        $('#modalEditar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al actualizar');
                    }

                })
                .catch((error) => {
                    toastr.error('Error al actualizar');
                    closeLoading();
                })
                .finally(() => {
                    // Reactivar botón al finalizar
                    resetButton();
                });


            // Función interna para restaurar el botón
            function resetButton() {
                btnGuardar.disabled = false;
                btnGuardar.textContent = 'Guardar';
            }
        }



        function infoMembresiaPanel(){
            var idcliente = document.getElementById('id-editarbloque').value;
            window.location.href="{{ url('/admin/historial/membresia/index') }}/" + idcliente;
        }













    </script>



            <script>
                (function () {
                    // ===== Config inicial =====
                    const SERVER_DEFAULT = {{ $predeterminado }}; // 0 = light, 1 = dark
                    const iconEl = document.getElementById('theme-icon');

                    // CSRF para axios
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

                    // ===== Funciones =====
                    function applyTheme(mode) {
                        const dark = mode === 'dark';

                        // AdminLTE v3
                        document.body.classList.toggle('dark-mode', dark);

                        // AdminLTE v4
                        document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');

                        // Icono
                        if (iconEl) {
                            iconEl.classList.remove('fa-sun', 'fa-moon');
                            iconEl.classList.add(dark ? 'fa-moon' : 'fa-sun');
                        }
                    }

                    function themeToInt(mode) {
                        return mode === 'dark' ? 1 : 0;
                    }

                    function intToTheme(v) {
                        return v === 1 ? 'dark' : 'light';
                    }

                    // ===== Aplicar tema inicial desde servidor =====
                    applyTheme(intToTheme(SERVER_DEFAULT));

                    // ===== Manejo de clicks y POST a backend =====
                    let saving = false;

                    document.addEventListener('click', async (e) => {
                        const a = e.target.closest('.dropdown-item[data-theme]');
                        if (!a) return;
                        e.preventDefault();
                        if (saving) return;

                        const selectedMode = a.dataset.theme; // 'dark' | 'light'
                        const newValue = themeToInt(selectedMode);

                        // Modo optimista: aplicar de una vez
                        const previousMode = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                        applyTheme(selectedMode);

                        try {
                            saving = true;
                            await axios.post('/admin/actualizar/tema', { tema: newValue });
                            // Si querés, mostrar un toast:
                            if (window.toastr) toastr.success('Tema actualizado');
                        } catch (err) {
                            // Revertir si falló
                            applyTheme(previousMode);
                            if (window.toastr) {
                                toastr.error('No se pudo actualizar el tema');
                            } else {
                                alert('No se pudo actualizar el tema');
                            }
                        } finally {
                            saving = false;
                        }
                    });
                })();
            </script>


@endsection
