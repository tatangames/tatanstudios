@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop
{{-- Activa plugins que necesitas --}}
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Toastr', true)
@section('plugins.Sweetalert2', true)

@section('content_top_nav_right')

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

    <div class="pc-container">
        <div class="pc-content">

            <div class="row">
                {{-- KPI --}}
                <div class="col-md-6 col-xl-3">
                    <div class="card bg-success">
                        <div class="card-body">
                            <h6 class="mb-2 text-white font-weight-bold">Usuarios
                                Registrados: {{ $totalRegistrados }}</h6>
                            <h6 class="mb-2 text-white font-weight-bold">Total Membresía
                                (abonos): {{ $totalMembresiaFormat }}</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card bg-primary">
                        <div class="card-body">
                            <h6 class="mb-2 text-white font-weight-bold">Total Gasto
                                Equipo: {{ $totalGastoEquipoFormat }}</h6>
                            <h6 class="mb-2 text-white font-weight-bold">Gasto en
                                Mantenimiento: {{ $totalGastoEquipoMantenimientoFormat }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CHARTS --}}
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                <span class="chart-title"
                      data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top"
                      title="Suma de abonos registrados por mes (incluye pagos iniciales si los registras como abono). Eje X: Mes/Año. Eje Y: USD.">
                    Ingresos mensuales <i class="fas fa-info-circle ml-1"></i>
                </span>
                        </div>
                        <div class="card-body">
                            <canvas id="ingresosChart" style="height:320px"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                <span class="chart-title"
                      data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top"
                      title="Distribución de las membresías actuales (is_actual=1) por estado según días restantes: Activas (>5), Por vencer (1–5), Hoy (0), Vencidas (<0).">
                    Estado de membresías <i class="fas fa-info-circle ml-1"></i>
                </span>
                        </div>
                        <div class="card-body">
                            <canvas id="estadoChart" style="height:320px"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                <span class="chart-title"
                      data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top"
                      title="Cantidad de usuarios creados por mes (usa fecha_registrado en usuarios).">
                    Clientes nuevos por mes <i class="fas fa-info-circle ml-1"></i>
                </span>
                        </div>
                        <div class="card-body">
                            <canvas id="clientesChart" style="height:300px"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                <span class="chart-title"
                      data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top"
                      title="Cobrado: suma total de abonos. Pendiente: Σ max(precio − abonos, 0) para membresías actuales (is_actual=1).">
                    Cobrado vs Pendiente <i class="fas fa-info-circle ml-1"></i>
                </span>
                        </div>
                        <div class="card-body">
                            <canvas id="cobradoChart" style="height:300px"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                <span class="chart-title"
                      data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top"
                      title="Suma de abonos agrupados por el plan (campo 'nombre' en usuario_membresia: Mensual, Trimestral, etc.).">
                    Ingresos por tipo de membresía <i class="fas fa-info-circle ml-1"></i>
                </span>
                        </div>
                        <div class="card-body">
                            <canvas id="planChart" style="height:300px"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                <span class="chart-title"
                      data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top"
                      title="Conteo de membresías actuales vencidas, agrupadas por días de atraso: 0–7, 8–30, 31–60, 60+.">
                    Membresías vencidas por antigüedad <i class="fas fa-info-circle ml-1"></i>
                </span>
                        </div>
                        <div class="card-body">
                            <canvas id="vencidasChart" style="height:300px"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="chart-title"
                  data-toggle="tooltip" data-bs-toggle="tooltip" data-placement="top"
                  title="Compara lo gastado (Maquinaria + Mantenimiento) contra lo ganado (Membresías). Neto = Ingresos − Gastos.">
                Costo-Beneficio (Gastos vs Ingresos) <i class="fas fa-info-circle ml-1"></i>
            </span>
                            <span id="cbNeto" class="badge badge-secondary" style="font-size: 0.9rem;">Neto: —</span>
                        </div>
                        <div class="card-body">
                            <canvas id="costoBeneficioChart" style="height:360px"></canvas>
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>

@stop

@section('js')
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/chart.js') }}"></script>

    <script>
        (function () {
            // Tooltip para Bootstrap 4 (AdminLTE 3)
            if (window.jQuery && typeof jQuery.fn.tooltip === 'function') {
                jQuery(function ($) {
                    $('[data-toggle="tooltip"]').tooltip();
                });
            }

            // Tooltip para Bootstrap 5 (AdminLTE 4)
            if (window.bootstrap && typeof bootstrap.Tooltip === 'function') {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el);
                });
            }
        })();
    </script>
    <style>
        .chart-title {
            cursor: help;
        }
    </style>

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
                document.body.classList.toggle('dark-mode', dark);                // AdminLTE v3
                document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light'); // AdminLTE v4
                if (iconEl) {
                    iconEl.classList.remove('fa-sun', 'fa-moon');
                    iconEl.classList.add(dark ? 'fa-moon' : 'fa-sun');
                }
            }

            const themeToInt = m => m === 'dark' ? 1 : 0;
            const intToTheme = v => v === 1 ? 'dark' : 'light';

            // Aplicar tema inicial
            applyTheme(intToTheme(SERVER_DEFAULT));

            // Manejo de clicks y POST a backend
            let saving = false;
            document.addEventListener('click', async (e) => {
                const a = e.target.closest('.dropdown-item[data-theme]');
                if (!a) return;
                e.preventDefault();
                if (saving) return;

                const selectedMode = a.dataset.theme;
                const prev = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                applyTheme(selectedMode);

                try {
                    saving = true;
                    await axios.post('/admin/actualizar/tema', {tema: themeToInt(selectedMode)});
                    if (window.toastr) toastr.success('Tema actualizado');
                } catch (err) {
                    applyTheme(prev);
                    if (window.toastr) toastr.error('No se pudo actualizar el tema');
                } finally {
                    saving = false;
                }
            });

            // ====== CHARTS ======
            const base = "{{ url('/admin/dashboard') }}";
            const ctx = id => document.getElementById(id).getContext('2d');

            function drawLine(id, labels, data, label) {
                return new Chart(ctx(id), {
                    type: 'line',
                    data: {labels, datasets: [{label, data, fill: true, tension: .3}]},
                    options: {responsive: true, maintainAspectRatio: false, scales: {y: {beginAtZero: true}}}
                });
            }

            function drawBar(id, labels, data, label) {
                return new Chart(ctx(id), {
                    type: 'bar',
                    data: {labels, datasets: [{label, data}]},
                    options: {responsive: true, maintainAspectRatio: false, scales: {y: {beginAtZero: true}}}
                });
            }

            function drawDoughnut(id, labels, data) {
                return new Chart(ctx(id), {
                    type: 'doughnut',
                    data: {labels, datasets: [{data}]},
                    options: {responsive: true, maintainAspectRatio: false}
                });
            }

            async function fetchJSON(url) {
                const r = await fetch(url);
                return r.json();
            }





            (async () => {
                try {
                    // ===== Ingresos mensuales =====
                    const ing = await fetchJSON(`${base}/ingresos-mensuales`);

                    // Formatear etiquetas YYYY-MM → Mes/Año
                    const meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
                    const etiquetas = ing.labels.map(l => {
                        const [y, m] = l.split("-");
                        return `${meses[parseInt(m) - 1]}/${y}`;
                    });

                    drawLine('ingresosChart', etiquetas, ing.data, 'USD');

                    // ===== Estado de membresías =====
                    const est = await fetchJSON(`${base}/estado-membresias`);
                    drawDoughnut('estadoChart', est.labels, est.data);

                    // ===== Clientes nuevos =====
                    const cli = await fetchJSON(`${base}/clientes-nuevos`);
                    drawBar('clientesChart', cli.labels, cli.data, 'Clientes');

                    // ===== Cobrado vs Pendiente =====
                    const cvp = await fetchJSON(`${base}/cobrado-vs-pendiente`);
                    drawDoughnut('cobradoChart', cvp.labels, cvp.data);

                    // ===== Ingresos por plan =====
                    const plan = await fetchJSON(`${base}/ingresos-por-plan`);
                    drawBar('planChart', plan.labels, plan.data, 'USD');

                    // ===== Vencidas por antigüedad =====
                    const ven = await fetchJSON(`${base}/vencidas-antiguedad`);
                    drawBar('vencidasChart', ven.labels, ven.data, 'Cantidad');


                    // ===== Costo-Beneficio: Gastos vs Ingresos =====
                    const cb = await fetchJSON(`${base}/costo-beneficio`);
                    drawBar('costoBeneficioChart', cb.labels, cb.data, 'USD');

                    // Mostrar el Neto en el título con color
                    (function(){
                        const badge = document.getElementById('cbNeto');
                        if (!badge || !cb.extra) return;
                        const neto = cb.extra.neto;

                        badge.textContent = `Neto: $${Number(neto).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}`;

                        // Quitar clases previas y aplicar color según signo
                        badge.classList.remove('badge-secondary', 'badge-success', 'badge-danger', 'badge-warning');
                        if (neto > 0)      badge.classList.add('badge-success');
                        else if (neto < 0) badge.classList.add('badge-danger');
                        else               badge.classList.add('badge-warning');
                    })();


                } catch (e) {
                    console.error(e);
                    if (window.toastr) toastr.error('No se pudieron cargar las gráficas');
                }
            })();

        })();
    </script>
@endsection
