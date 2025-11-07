@extends('adminlte::page')

@section('title', 'Historial Membresia')

@section('meta_tags')
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ultra.jpg') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/ultra.jpg') }}">
@endsection

@section('content_header')
    <h1>Historial Membresia</h1>
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
        <section class="content-header">
            <div class="container-fluid">

            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-gray-dark">
                    <div class="card-header">
                        <h3 class="card-title">Lista</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="tablaDatatable"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <div class="modal fade" id="modalEditar">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Editar Registro</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-editar">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="form-group">
                                            <input type="hidden" id="id-editar">
                                        </div>

                                        <div class="form-group">
                                            <label>Nombre <span style="color: red">*</span></label>
                                            <input type="text" maxlength="100" class="form-control" id="nombre-editar" placeholder="Nombre">
                                        </div>

                                        <div class="form-group">
                                            <label>Fecha Inicio <span style="color: red">*</span></label>
                                            <input type="date" class="form-control" id="fechainicio-editar">
                                        </div>

                                        <div class="form-group">
                                            <label>Fecha Finaliza <span style="color: red">*</span></label>
                                            <input type="date" class="form-control" id="fechafinaliza-editar">
                                        </div>


                                        <div class="form-group">
                                            <label>Precio <span style="color: red">*</span></label>
                                            <input type="number" class="form-control" id="precio-editar" placeholder="Precio">
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" style="font-weight: bold; background-color: #28a745; color: white !important;"
                                class="btn btn-success btn-sm" onclick="editar()">Guardar</button>
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
                $(function () {

                    const idcliente = {{ $idcliente }};

                    const ruta = "{{ url('/admin/historial/membresia/tabla') }}/" + idcliente;

                    function initDataTable() {
                        // Si ya hay instancia, destrúyela antes de re-crear
                        if ($.fn.DataTable.isDataTable('#tabla')) {
                            $('#tabla').DataTable().destroy();
                        }

                        // Inicializa
                        $('#tabla').DataTable({
                            paging: true,
                            lengthChange: true,
                            searching: true,
                            ordering: true,
                            info: true,
                            autoWidth: false,
                            responsive: true,
                            pagingType: "full_numbers",
                            lengthMenu: [[10, 25, 50, 100, 150, -1],[10, 25, 50, 100, 150, "Todo"]],
                            language: {
                                sProcessing: "Procesando...",
                                sLengthMenu: "Mostrar _MENU_ registros",
                                sZeroRecords: "No se encontraron resultados",
                                sEmptyTable: "Ningún dato disponible en esta tabla",
                                sInfo: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                sInfoEmpty: "Mostrando 0 a 0 de 0 registros",
                                sInfoFiltered: "(filtrado de _MAX_ registros)",
                                sSearch: "Buscar:",
                                oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" },
                                oAria: { sSortAscending: ": Orden ascendente", sSortDescending: ": Orden descendente" }
                            },
                            dom:
                                "<'row align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-right'f>>" +
                                "tr" +
                                "<'row align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
                        });

                        // Estilitos
                        $('#tabla_length select').addClass('form-control form-control-sm');
                        $('#tabla_filter input').addClass('form-control form-control-sm').css('display','inline-block');
                    }

                    function cargarTabla() {
                        $('#tablaDatatable').load(ruta, function() {
                            // AQUI debe existir exactamente un <table id="tabla"> en la parcial
                            initDataTable();
                        });
                    }

                    // Primera carga
                    cargarTabla();

                    // Exponer recarga para tus flujos (crear/editar)
                    window.recargar = function () {
                        cargarTabla();
                    };
                });
            </script>


            <script>
                function verInformacion(id){
                    openLoading();
                    document.getElementById("formulario-editar").reset();

                    axios.post('/admin/historial/membresia/informacion',{
                        'id': id
                    })
                        .then((response) => {
                            closeLoading();
                            if(response.data.success === 1){
                                $('#modalEditar').modal('show');
                                $('#id-editar').val(response.data.info.id);

                                $('#nombre-editar').val(response.data.info.nombre);
                                $('#fechainicio-editar').val(response.data.info.fecha_inicio);
                                $('#fechafinaliza-editar').val(response.data.info.fecha_fin);
                                $('#precio-editar').val(response.data.info.precio);

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
                    var nombre = document.getElementById('nombre-editar').value;
                    var fechaInicio = document.getElementById('fechainicio-editar').value;
                    var fechaFin = document.getElementById('fechafinaliza-editar').value;
                    var precio = document.getElementById('precio-editar').value;


                    if(nombre === ''){
                        toastr.error('Nombre es requerido');
                        return;
                    }

                    if(fechaInicio === ''){
                        toastr.error('Fecha inicio es requerido');
                        return;
                    }

                    if(fechaFin === ''){
                        toastr.error('Fecha fin es requerido');
                        return;
                    }

                    var reglaNumeroDiesDecimal = /^([0-9]+\.?[0-9]{0,10})$/;

                    if(precio === ''){
                        toastr.error('Precio es requerido');
                        return;
                    }

                    if(!precio.match(reglaNumeroDiesDecimal)) {
                        toastr.error('Precio debe ser número Decimal');
                        return;
                    }

                    if(precio < 0){
                        toastr.error('Precio no debe ser negativo');
                        return;
                    }

                    if(precio > 9000000){
                        toastr.error('Precio debe ser máximo 9 millones');
                        return;
                    }

                    if (fechaInicio && fechaFin) {
                        var inicio = new Date(fechaInicio);
                        var fin = new Date(fechaFin);

                        if (inicio > fin) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Fechas inválidas',
                                text: 'La fecha de inicio no puede ser mayor que la fecha de finalización.',
                                confirmButtonColor: '#3085d6'
                            });
                            return; // detener ejecución o envío del formulario
                        }
                    }




                    openLoading();
                    var formData = new FormData();
                    formData.append('id', id);
                    formData.append('nombre', nombre);
                    formData.append('fechainicio', fechaInicio);
                    formData.append('fechafin', fechaFin);
                    formData.append('precio', precio);

                    axios.post('/admin/historial/membresia/actualizar', formData, {
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
                        });
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
