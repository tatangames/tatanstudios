@extends('adminlte::page')

@section('title', 'Nuevo Cliente')

@section('meta_tags')
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ultra.jpg') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/ultra.jpg') }}">
@endsection

@section('content_header')
    <h1>Nuevo Cliente</h1>
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


    <div class="row g-3">
        <!-- BLOQUE IZQUIERDO -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-body py-3">
                    <form id="formulario-nuevo">
                        <div class="form-group mb-2">
                            <label class="mb-1">Nombre <span style="color: red">*</span></label>
                            <input type="text" maxlength="100" autocomplete="off" class="form-control form-control-sm" id="nombre-nuevo" placeholder="Nombre">
                        </div>

                        <div class="form-group mb-2">
                            <label class="mb-1">Correo</label>
                            <input type="text" maxlength="100" autocomplete="off" class="form-control form-control-sm" id="correo-nuevo" placeholder="Correo electrónico">
                        </div>

                        <div class="form-group mb-2">
                            <label class="mb-1">Teléfono</label>
                            <input type="text" maxlength="25" autocomplete="off" class="form-control form-control-sm" id="telefono-nuevo" placeholder="Teléfono">
                        </div>

                        <div class="form-group mb-3" style="max-width: 30%">
                            <label class="mb-1">Fecha de Nacimiento <span style="color: red">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="fechanacimiento-nuevo" placeholder="Fecha de Nacimiento">
                        </div>

                        <div class="form-group mb-3" style="max-width: 30%">
                            <label class="mb-1">Sexo <span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="sexo-nuevo">
                                <option value="">Seleccione...</option>
                                <option value="1">Masculino</option>
                                <option value="0">Femenino</option>
                            </select>
                        </div>

                        <hr>

                        <div class="form-group mb-3" style="max-width: 100%">
                            <label class="mb-1">Membresía <span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="membresia-nuevo">
                                <option value="">Seleccionar Opción</option>
                                @foreach($arrayMembresias as $item)
                                    <option value="{{$item->id}}">{{ $item->nombreCompleto }}</option>
                                @endforeach
                            </select>
                        </div>



                        <div class="form-group mb-2" style="max-width: 20%">
                            <label class="mb-1">En caso de Abono</label>
                            <input type="number" autocomplete="off" class="form-control form-control-sm" id="abono-nuevo" placeholder="Abono">
                        </div>


                        <br>
                        <br>

                        <div class="text-right mt-4">
                            <button type="button" id="btn-guardar" class="btn btn-success btn-sm" onclick="nuevo()">
                                <i class="fas fa-save"></i> Guardar
                            </button>
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
                    <form id="formulario2-nuevo">
                        <div class="form-group mb-2">
                            <label class="mb-1">Nombre</label>
                            <input type="text" maxlength="100" autocomplete="off" class="form-control form-control-sm" id="nombreemergencia-nuevo" placeholder="Nombre">
                        </div>

                        <div class="form-group mb-2">
                            <label class="mb-1">Teléfono</label>
                            <input type="text" maxlength="25" autocomplete="off" class="form-control form-control-sm" id="telefonoemergencia-nuevo" placeholder="Teléfono">
                        </div>

                        <div class="form-group mb-2">
                            <label class="mb-1">Condición Médica</label>
                            <input type="text" maxlength="800" autocomplete="off" class="form-control form-control-sm" id="condicionemergencia-nuevo" placeholder="Condición">
                        </div>
                    </form>
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

        function nuevo(){
            var nombre = document.getElementById('nombre-nuevo').value; // requerido
            var correo = document.getElementById('correo-nuevo').value;
            var telefono = document.getElementById('telefono-nuevo').value;
            var fechanac = document.getElementById('fechanacimiento-nuevo').value; // requerido
            var sexo = document.getElementById('sexo-nuevo').value; // requerido
            var membresia = document.getElementById('membresia-nuevo').value; // requerido
            var abono = document.getElementById('abono-nuevo').value;

            var nombreEmergencia = document.getElementById('nombreemergencia-nuevo').value;
            var telefonoEmergencia = document.getElementById('telefonoemergencia-nuevo').value;
            var condicionEmergencia = document.getElementById('condicionemergencia-nuevo').value;

            const form1    = document.getElementById('formulario-nuevo');
            const form2    = document.getElementById('formulario2-nuevo');


            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }


            if(sexo === ''){
                toastr.error('Seleccionar Sexo del Cliente');
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

            const btnGuardar = document.getElementById('btn-guardar');
            // Desactivar botón al iniciar
            btnGuardar.disabled = true;
            btnGuardar.textContent = 'Guardando...';

            openLoading();
            var formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('correo', correo);
            formData.append('telefono', telefono);
            formData.append('fechanac', fechanac);
            formData.append('sexo', sexo);
            formData.append('membresia', membresia);
            formData.append('abono', abono);

            formData.append('nombreEmergencia', nombreEmergencia);
            formData.append('telefonoEmergencia', telefonoEmergencia);
            formData.append('condicionEmergencia', condicionEmergencia);

            axios.post('/admin/cliente/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        toastr.success('Registrado correctamente');
                        form1.reset()
                        form2.reset()
                    }
                    else {
                        toastr.error('Error al registrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al registrar');
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
