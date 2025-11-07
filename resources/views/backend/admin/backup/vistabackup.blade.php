@extends('adminlte::page')

@section('title', 'Backup')

@section('meta_tags')
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ultra.jpg') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/ultra.jpg') }}">
@endsection
@section('content_header')
    <h1>Backup</h1>
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-database mr-1"></i> Generar copia de seguridad (SQL)
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Descarga una copia de la base de datos en formato <b>.sql</b>.
                    </p>

                    <button id="btn-backup-sql" class="btn btn-primary">
                        <i class="fas fa-database"></i> Copia BD (.sql)
                    </button>

                    <div class="alert alert-info mt-3 mb-0">
                        <ul class="mb-0 pl-3">
                            <li>El archivo se descargará automáticamente al finalizar.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- (Opcional) área de logs/resultados --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-list-alt mr-1"></i> Resultado
                    </h3>
                </div>
                <div class="card-body">
                    <pre id="result-log" class="mb-0" style="white-space: pre-wrap;">Listo para generar una copia…</pre>
                </div>
            </div>
        </div>


    </div>

@stop

@section('js')
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script>
        (function () {
            // ===== Config inicial =====
            const SERVER_DEFAULT = {{ $predeterminado }}; // 0 = light, 1 = dark
            const iconEl = document.getElementById('theme-icon');

            // CSRF para axios
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

            // ===== Funciones de tema =====
            function applyTheme(mode) {
                const dark = mode === 'dark';
                document.body.classList.toggle('dark-mode', dark);               // AdminLTE v3
                document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light'); // AdminLTE v4
                if (iconEl) {
                    iconEl.classList.remove('fa-sun', 'fa-moon');
                    iconEl.classList.add(dark ? 'fa-moon' : 'fa-sun');
                }
            }
            function themeToInt(mode) { return mode === 'dark' ? 1 : 0; }
            function intToTheme(v) { return v === 1 ? 'dark' : 'light'; }
            applyTheme(intToTheme(SERVER_DEFAULT));

            let saving = false;
            document.addEventListener('click', async (e) => {
                const a = e.target.closest('.dropdown-item[data-theme]');
                if (!a) return;
                e.preventDefault();
                if (saving) return;
                const selectedMode = a.dataset.theme;
                const newValue = themeToInt(selectedMode);
                const previousMode = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                applyTheme(selectedMode);
                try {
                    saving = true;
                    await axios.post('/admin/actualizar/tema', { tema: newValue });
                    if (window.toastr) toastr.success('Tema actualizado');
                } catch (err) {
                    applyTheme(previousMode);
                    if (window.toastr) toastr.error('No se pudo actualizar el tema');
                    else alert('No se pudo actualizar el tema');
                } finally { saving = false; }
            });

            // ====== Backup (solo SQL) ======
            const btnSql = document.getElementById('btn-backup-sql');
            const logEl  = document.getElementById('result-log');

            function setBusy(busy) {
                if (!btnSql) return;
                btnSql.disabled = busy;
                btnSql.classList.toggle('disabled', busy);
            }

            function downloadBlob(blob, filename) {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            }

            function timestamp() {
                const d = new Date();
                const pad = n => (n < 10 ? '0' + n : n);
                return d.getFullYear().toString() +
                    pad(d.getMonth() + 1) +
                    pad(d.getDate()) + '_' +
                    pad(d.getHours()) +
                    pad(d.getMinutes()) +
                    pad(d.getSeconds());
            }

            async function runBackupSql() {
                const url = "{{ route('admin.backup.db') }}";
                setBusy(true);

                if (window.Swal) {
                    Swal.fire({
                        title: 'Generando copia…',
                        text: 'No cierres esta ventana',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                }

                logEl.textContent = 'Iniciando backup SQL…';

                try {
                    const resp = await axios.post(url, {}, { responseType: 'blob' });

                    // Nombre a partir de cabecera o timestamp
                    let filename = `Backup_SQL_${timestamp()}.sql`;
                    const dispo = resp.headers['content-disposition'] || '';
                    const match = /filename="?([^"]+)"?/i.exec(dispo);
                    if (match && match[1]) filename = match[1];

                    downloadBlob(resp.data, filename);

                    if (window.toastr) toastr.success('Copia SQL lista para descargar');
                    logEl.textContent = `Backup generado correctamente (${filename}).`;
                } catch (err) {
                    let msg = 'Ocurrió un error generando el backup';
                    try {
                        if (err.response && err.response.data) {
                            const ct = err.response.headers?.['content-type'] || '';
                            if (ct.includes('application/json')) {
                                const reader = new FileReader();
                                reader.onload = () => {
                                    try {
                                        const j = JSON.parse(reader.result);
                                        logEl.textContent = (j.message || msg) + (j.error ? `\nDetalle: ${j.error}` : '');
                                    } catch {
                                        logEl.textContent = msg;
                                    }
                                };
                                reader.readAsText(err.response.data);
                            } else {
                                logEl.textContent = msg;
                            }
                        } else {
                            logEl.textContent = msg;
                        }
                    } catch {
                        logEl.textContent = msg;
                    }
                    if (window.toastr) toastr.error('No se pudo generar la copia');
                } finally {
                    if (window.Swal) Swal.close();
                    setBusy(false);
                }
            }

            if (btnSql) {
                btnSql.addEventListener('click', (e) => {
                    e.preventDefault();
                    runBackupSql();
                });
            }
        })();
    </script>
@endsection
