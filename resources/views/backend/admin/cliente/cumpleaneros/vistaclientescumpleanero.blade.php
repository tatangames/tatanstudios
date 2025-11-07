@extends('adminlte::page')

@section('title', 'Clientes Cumplen Hoy')

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
          <section class="content">
            <div class="container-fluid">
                <div class="card card-gray-dark">
                    <div class="card-header">
                        <h3 class="card-title">Clientes que cumplen años en el mes de <strong>{{ $fechaActual }}</strong></h3>
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

    @stop
@section('js')
    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

        <script>
            // ===== Plugin de orden para DD-MM-YYYY (date-eu), registrado una sola vez =====
            (function registerDateEuSortOnce(){
                if (window.__dateEuSortRegistered) return;
                window.__dateEuSortRegistered = true;

                $.fn.dataTable.ext.type.detect.push(function (d) {
                    if (typeof d !== 'string') return null;
                    d = d.trim();
                    if (d === '' || d === '—' || d === '-') return 'date-eu';
                    return /^\d{2}-\d{2}-\d{4}$/.test(d) ? 'date-eu' : null;
                });

                $.extend($.fn.dataTable.ext.type.order, {
                    'date-eu-pre': function (d) {
                        if (typeof d !== 'string') return 0;
                        d = d.trim();
                        if (d === '' || d === '—' || d === '-') return 0;
                        var parts = d.split('-');
                        if (parts.length !== 3) return 0;
                        var dd = parts[0], mm = parts[1], yy = parts[2];
                        var n = parseInt(yy + mm + dd, 10);
                        return isNaN(n) ? 0 : n;
                    }
                });
            })();

            $(function () {
                const ruta = "{{ url('/admin/cliente/listado/cumplenhoy/tabla') }}";

                function buildDateEuTargets() {
                    // Detecta índices de columnas marcadas con data-fecha-eu en el thead
                    const $ths = $('#tabla thead th');
                    let targets = [];
                    $ths.each(function(i, th){
                        if (th.hasAttribute('data-fecha-eu')) targets.push(i);
                    });
                    // Fallback: si no hay ninguna marcada, usar la columna 0
                    if (targets.length === 0) targets = [0];
                    return targets;
                }

                function initDataTable() {
                    const $tabla = $('#tabla');
                    if ($tabla.length === 0) {
                        console.warn('No se encontró #tabla dentro del HTML cargado.');
                        return;
                    }

                    if ($.fn.DataTable.isDataTable('#tabla')) {
                        $('#tabla').DataTable().destroy();
                    }

                    const fechaCols = buildDateEuTargets();
                    const ordenInicial = [[fechaCols[0], 'desc']]; // orden por la primera columna de fecha

                    $('#tabla').DataTable({
                        order: ordenInicial,
                        columnDefs: [
                            { type: 'date-eu', targets: fechaCols }
                        ],
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

                    // Estilos
                    $('#tabla_length select').addClass('form-control form-control-sm');
                    $('#tabla_filter input').addClass('form-control form-control-sm').css('display','inline-block');
                }

                function cargarTabla() {
                    $('#tablaDatatable').load(ruta, function(response, status){
                        if (status !== 'success') {
                            console.error('Error al cargar la tabla:', status);
                            return;
                        }
                        // IMPORTANTE: en la parcial, pon el/los <th> de fecha así:
                        // <th data-fecha-eu>Fecha fin</th>  (o cualquier columna con DD-MM-YYYY)
                        initDataTable();
                    });
                }

                // Primera carga
                cargarTabla();

                // Exponer recarga para flujos de crear/editar/borrar
                window.recargar = function () {
                    cargarTabla();
                };
            });
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
