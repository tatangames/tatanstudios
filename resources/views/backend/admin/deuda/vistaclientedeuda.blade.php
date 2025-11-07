@extends('adminlte::page')

@section('title', 'Clientes Deuda')

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
                    <div class="card-header">
                        <h3 class="card-title">Lista de Clientes con Deuda</h3>
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

                                    <input type="hidden" id="id-editar">
                                    <input type="hidden" id="id-editarmembresia">

                                    <div class="row g-3">
                                        <!-- BLOQUE IZQUIERDO -->
                                        <div class="col-md-6">
                                            <div class="card card-primary">
                                                <div class="card-body py-3">
                                                    <form id="formulario-editar">
                                                        <div class="form-group mb-2">
                                                            <label class="mb-1">Cliente</label>
                                                            <input type="text" id="cliente-nombre" class="form-control form-control-sm" readonly>
                                                        </div>

                                                        <div class="form-group mb-2">
                                                            <label class="mb-1">Teléfono</label>
                                                            <input type="text" id="cliente-telefono" class="form-control form-control-sm" readonly>
                                                        </div>

                                                        <hr>

                                                        <div class="form-group mb-2">
                                                            <label class="mb-1">Membresía</label>
                                                            <input type="text" id="membresia-nombre" class="form-control form-control-sm" readonly>
                                                        </div>

                                                        <div class="form-group mb-2">
                                                            <label class="mb-1">Precio</label>
                                                            <input type="text" id="membresia-precio" class="form-control form-control-sm" readonly>
                                                        </div>


                                                        <div class="form-group mb-2">
                                                            <label class="mb-1">Total Pagado</label>
                                                            <input type="text" id="membresia-total-pagado" class="form-control form-control-sm" readonly>
                                                        </div>

                                                        <div class="form-group mb-2">
                                                            <label class="mb-1">Adeudo</label>
                                                            <input type="text" id="membresia-adeudo" class="form-control form-control-sm font-weight-bold" readonly>
                                                        </div>

                                                        <div class="form-group mb-2">
                                                            <label class="mb-1">Estado de pago</label><br>
                                                            <span id="badge-estado-pago" class="badge">—</span>
                                                        </div>

                                                        <hr>

                                                        <div class="form-row">
                                                            <div class="form-group col-6">
                                                                <label class="mb-1">Fecha Inicio</label>
                                                                <input type="text" id="membresia-inicio" class="form-control form-control-sm" readonly>
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label class="mb-1">Fecha Fin</label>
                                                                <input type="text" id="membresia-fin" class="form-control form-control-sm" readonly>
                                                            </div>
                                                        </div>

                                                        <div class="form-group mb-2">
                                                            <label class="mb-1">Días restantes</label>
                                                            <input type="text" id="membresia-dias-restantes" class="form-control form-control-sm" readonly>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- BLOQUE DERECHO: Historial de abonos -->
                                        <div class="col-md-6">


                                            <!-- Campo para abonar -->
                                            <div class="mb-3">
                                                <label class="mb-1 font-weight-bold">Nuevo Abono</label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="number" step="0.01" min="0" class="form-control" id="monto-abono" placeholder="Ingrese monto">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-success" type="button" id="btn-abonar" onclick="registrarAbono()">
                                                            <i class="fas fa-plus"></i> Abonar
                                                        </button>
                                                    </div>
                                                </div>
                                                <small id="ayuda-abono" class="form-text text-muted">El monto se sumará al historial de abonos.</small>
                                            </div>

                                            <hr>



                                            <div class="card card-secondary">
                                                <div class="card-body py-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0" style="font-weight: bold">Historial de Abonos</h6>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped mb-0">
                                                            <thead>
                                                            <tr>
                                                                <th style="width: 90px;">Fecha</th>
                                                                <th class="text-right">Monto</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody id="tbody-abonos">
                                                            <tr><td colspan="2" class="text-center text-muted">Sin abonos</td></tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- row g-3 -->

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





        @stop



        @section('js')
            <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>

            <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
            <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

            <script>

                function registrarAbono() {
                    const idMembresia = document.getElementById('id-editarmembresia').value;
                    const montoInput = document.getElementById('monto-abono');
                    const abono = parseFloat(montoInput.value);

                    if (!idMembresia) {
                        toastr.error('No se encontró la membresía.');
                        return;
                    }


                    if (!abono || abono <= 0) {
                        toastr.warning('Ingrese un monto válido.');
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

                    const btnAbonar = document.getElementById('btn-abonar');
                    btnAbonar.disabled = true;
                    btnAbonar.textContent = 'Guardando...';

                    openLoading();

                    axios.post('/admin/cliente-deuda/abonar', {
                        id_membresia: idMembresia,
                        monto: abono
                    })
                        .then(response => {
                            closeLoading();

                            const res = response.data;

                            if (res.success === 1) {
                                toastr.success('Abono registrado correctamente.');
                                montoInput.value = '';
                                // Recargar información del cliente (no de la membresía)
                                verInformacion(document.getElementById('id-editar').value);

                                recargar();
                            }
                            else if (res.success === 2) {
                                toastr.warning(`El abono supera el monto pendiente ($${res.pendiente}).`);
                            }
                            else {
                                toastr.error('No se pudo registrar el abono.');
                            }
                        })
                        .catch(() => {
                            closeLoading();
                            toastr.error('Error al registrar el abono.');
                        })
                        .finally(() => {
                            // Reactivar botón al finalizar
                            resetButton();
                        });


                    // Función interna para restaurar el botón
                    function resetButton() {
                        btnAbonar.disabled = false;
                        btnAbonar.innerHTML = '<i class="fas fa-plus"></i> Abonar';
                    }
                }

            </script>


            <script>
                // ===== Plugins de orden: fecha DD-MM-YYYY y moneda =====
                (function registerCustomSortersOnce(){
                    if (!window.__dateEuSortRegistered) {
                        window.__dateEuSortRegistered = true;

                        // Detecta fechas DD-MM-YYYY (o vacío/—/-) como 'date-eu'
                        $.fn.dataTable.ext.type.detect.push(function (d) {
                            if (typeof d !== 'string') return null;
                            d = d.trim();
                            if (d === '' || d === '—' || d === '-') return 'date-eu';
                            return /^\d{2}-\d{2}-\d{4}$/.test(d) ? 'date-eu' : null;
                        });

                        // Convierte DD-MM-YYYY a número YYYYMMDD
                        $.extend($.fn.dataTable.ext.type.order, {
                            'date-eu-pre': function (d) {
                                if (typeof d !== 'string') return 0;
                                d = d.trim();
                                if (d === '' || d === '—' || d === '-') return 0;
                                var p = d.split('-');
                                if (p.length !== 3) return 0;
                                var dd = p[0], mm = p[1], yy = p[2];
                                var n = parseInt(yy + mm + dd, 10);
                                return isNaN(n) ? 0 : n;
                            }
                        });
                    }

                    if (!window.__currencySortRegistered) {
                        window.__currencySortRegistered = true;

                        // Detector básico de moneda (si ve dígitos+separadores o símbolo $/€ etc.)
                        $.fn.dataTable.ext.type.detect.push(function (d) {
                            if (typeof d !== 'string') return null;
                            var s = d.trim();
                            if (s === '' || s === '—' || s === '-') return 'currency-any';
                            // algo como $1,234.56 | 1.234,56 | -$ 500 | Q 1,200
                            return /[-+]?[\s\$€£Q₡₲₺₹¥₦₱R$]*\d{1,3}([.,]\d{3})*([.,]\d{2})?/.test(s) ? 'currency-any' : null;
                        });

                        $.extend($.fn.dataTable.ext.type.order, {
                            'currency-any-pre': function (d) {
                                if (typeof d !== 'string') return 0;
                                var s = d.trim();
                                if (s === '' || s === '—' || s === '-') return 0;

                                // Quitar símbolos y espacios
                                s = s.replace(/[^\d.,\-+]/g, '');

                                // Si tiene coma y punto, asumimos: coma = miles, punto = decimal (1,234.56)
                                if (s.indexOf(',') > -1 && s.indexOf('.') > -1) {
                                    s = s.replace(/,/g, ''); // quitar miles
                                } else if (s.indexOf(',') > -1 && s.indexOf('.') === -1) {
                                    // Solo coma -> tratar como decimal (1.234,56 o 123,45)
                                    s = s.replace(/\./g, ''); // por si trae miles con punto
                                    s = s.replace(/,/g, '.'); // coma a punto decimal
                                } else {
                                    // Solo punto o solo número -> ya está OK
                                }

                                var n = parseFloat(s);
                                return isNaN(n) ? 0 : n;
                            }
                        });
                    }
                })();

                $(function () {
                    const ruta = "{{ url('/admin/cliente-deuda/listado/tabla') }}";

                    function getTargetsByAttr(attrName) {
                        const $ths = $('#tabla thead th');
                        let targets = [];
                        $ths.each(function(i, th){
                            if (th.hasAttribute(attrName)) targets.push(i);
                        });
                        return targets;
                    }

                    function resolveInitialOrder() {
                        const fechaCols = getTargetsByAttr('data-fecha-eu');
                        const moneyCols = getTargetsByAttr('data-moneda');

                        // Prioridad: fecha desc si existe; si no, moneda desc; si no, col 0 desc.
                        if (fechaCols.length) return [[fechaCols[0], 'desc']];
                        if (moneyCols.length) return [[moneyCols[0], 'desc']];
                        return [[0, 'desc']];
                    }

                    function buildColumnDefs() {
                        const defs = [];
                        const fechaCols = getTargetsByAttr('data-fecha-eu');
                        const moneyCols = getTargetsByAttr('data-moneda');

                        if (fechaCols.length) defs.push({ targets: fechaCols, type: 'date-eu' });
                        if (moneyCols.length) defs.push({ targets: moneyCols, type: 'currency-any' });

                        // Si no hay marcas, forzamos col 0 como fecha por compatibilidad con tu flujo previo
                        if (!fechaCols.length && !moneyCols.length) {
                            defs.push({ targets: 0, type: 'date-eu' });
                        }
                        return defs;
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

                        $('#tabla').DataTable({
                            order: resolveInitialOrder(),
                            columnDefs: buildColumnDefs(),
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
                            // En tu parcial, marca los <th> así según necesites:
                            // <th data-fecha-eu>Fecha</th>
                            // <th data-moneda>Pendiente</th>
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

                function recargar(){
                    var ruta = "{{ url('/admin/cliente-deuda/listado/tabla') }}";
                    $('#tablaDatatable').load(ruta);
                }

                // ver informacion de abonos con fecha y monto
                function verInformacion(id) {
                    openLoading();

                    // limpia
                    const form = document.getElementById("formulario-editar");
                    form && form.reset();
                    document.getElementById('tbody-abonos').innerHTML =
                        '<tr><td colspan="2" class="text-center text-muted">Sin abonos</td></tr>';

                    axios.post('/admin/cliente-deuda/informacion', { id })
                        .then((response) => {
                            closeLoading();

                            if (response.data.success === 1) {
                                const data = response.data; // ajustado a tu backend: success === 1

                                // Cliente
                                document.getElementById('id-editar').value        = data.cliente.id ?? '';
                                document.getElementById('id-editarmembresia').value   = data.membresia.id ?? '';


                                document.getElementById('cliente-nombre').value   = data.cliente.nombre ?? '';
                                document.getElementById('cliente-telefono').value = data.cliente.telefono ?? '';

                                // Membresía (puede venir null)
                                if (data.membresia) {
                                    const m = data.membresia;

                                    document.getElementById('membresia-nombre').value        = m.nombre ?? '';
                                    document.getElementById('membresia-precio').value        = formatMoney(data.precio ?? m.precio ?? 0);
                                    document.getElementById('membresia-total-pagado').value  = formatMoney(data.total_pagado ?? 0);
                                    document.getElementById('membresia-adeudo').value        = formatMoney(data.adeudo ?? 0);
                                    document.getElementById('membresia-inicio').value        = m.fecha_inicio ?? '';
                                    document.getElementById('membresia-fin').value           = m.fecha_fin ?? '';
                                    document.getElementById('membresia-dias-restantes').value= (m.dias_restantes ?? 0).toString();

                                    // Badge estado de pago
                                    const badge = document.getElementById('badge-estado-pago');
                                    const adeuda = !!data.adeuda;
                                    badge.textContent = adeuda ? 'Adeuda' : 'Solvente';
                                    badge.className = 'badge ' + (adeuda ? 'bg-danger' : 'bg-success');

                                    // Tabla de abonos
                                    const abonos = Array.isArray(data.historial_abonos) ? data.historial_abonos : [];
                                    const tbody = document.getElementById('tbody-abonos');
                                    if (abonos.length) {
                                        tbody.innerHTML = abonos.map(a => `
              <tr>
                <td>${escapeHtml(a.fecha_pago ?? '')}</td>
                <td class="text-right">${formatMoney(a.monto ?? 0)}</td>
              </tr>
            `).join('');
                                    } else {
                                        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Sin abonos</td></tr>';
                                    }

                                } else {
                                    // Sin membresía
                                    document.getElementById('membresia-nombre').value        = 'Sin membresía';
                                    document.getElementById('membresia-precio').value        = formatMoney(0);
                                    document.getElementById('membresia-total-pagado').value  = formatMoney(0);
                                    document.getElementById('membresia-adeudo').value        = formatMoney(0);
                                    document.getElementById('membresia-inicio').value        = '';
                                    document.getElementById('membresia-fin').value           = '';
                                    document.getElementById('membresia-dias-restantes').value= '';

                                    const badge = document.getElementById('badge-estado-pago');
                                    badge.textContent = 'Sin membresía';
                                    badge.className = 'badge bg-secondary';

                                    document.getElementById('tbody-abonos').innerHTML =
                                        '<tr><td colspan="2" class="text-center text-muted">Sin abonos</td></tr>';
                                }

                                // Mostrar modal
                                $('#modalEditar').modal('show');

                            } else {
                                toastr.error('Información no encontrada');
                            }
                        })
                        .catch(() => {
                            closeLoading();
                            toastr.error('Información no encontrada');
                        });
                }


                // Helpers
                function formatMoney(n) {
                    const num = Number(n || 0);
                    return '$' + num.toFixed(2);
                }
                function escapeHtml(str) {
                    if (str == null) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
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
