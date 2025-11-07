<!DOCTYPE html>
<html lang="es">

<head>
    <title>ULTRA FITNNES</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/login/bootstrap.min.css') }}">

    <!-- icono del sistema -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/zona-fitness-favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/zona-fitness-favicon-16x16.png') }}">
    <!-- libreria -->
    <link href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" type="text/css" rel="stylesheet" />

    <!-- estilo de toast -->
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">
    <!-- estilo de sweet -->
    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">

    <style>
        html, body {
            height: 90%;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-image: url({{ asset('images/logofondo.png') }});
            background-size: cover;
            background-repeat: no-repeat;

        }

        .demo-container {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .btn-lg {
            padding: 12px 26px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        ::placeholder {
            font-size:14px;
            letter-spacing:0.5px;
        }

        .form-control-lg {
            font-size: 16px;
            padding: 25px 20px;
        }
        .font-500{
            font-weight:500;
        }
        .image-size-small{
            width:140px;
            margin:0 auto;
        }
        .image-size-small img{
            width:140px;
            margin-bottom:-70px;
        }

    </style>
</head>

<body>
<div class="container">
    <div>
        <div class="demo-container" style="margin-top: 25px">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-12 mx-auto">
                        <div class="text-center image-size-small position-relative">
                            <img src="{{ asset('images/ultra.jpg') }}" class="rounded-circle p-2 bg-white">
                        </div>
                        <div class="p-5 bg-white rounded shadow-lg">
                            <h3 class="mb-2 text-center pt-5"><strong>ULTRA FITNESS</strong></h3>
                            <form>
                                <label style="margin-top: 10px" class="font-500">Usuario</label>
                                <input class="form-control form-control-lg mb-3" id="usuario" autocomplete="off" type="text">
                                <label class="font-500">Contraseña</label>
                                <input class="form-control form-control-lg" id="password" type="password">

                                <input type="button" value="Entrar" style="margin-top: 15px" onclick="login()" class="btn btn-primary btn-lg w-100 shadow-lg">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/sweetalert2.all.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/alertaPersonalizada.js') }}"></script>


<script type="text/javascript">

    // onkey Enter
    var input = document.getElementById("password");
    input.addEventListener("keyup", function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            login();
        }
    });

    // inicio de sesion
    function login() {

        var usuario = document.getElementById('usuario').value;
        var password = document.getElementById('password').value;

        if(usuario === ''){
            toastr.error('usuario es requerido');
            return;
        }

        if(password === ''){
            toastr.error('contraseña es requerida');
            return;
        }

        openLoading();

        let formData = new FormData();
        formData.append('usuario', usuario);
        formData.append('password', password);

        axios.post('/login', formData, {
        })
            .then((response) => {
                closeLoading();
                verificar(response);
            })
            .catch((error) => {
                toastr.error('error al iniciar sesión');
                closeLoading();
            });
    }

    // estados de la verificacion
    function verificar(response) {

        if (response.data.success === 0) {
            toastr.error('validación incorrecta')
        } else if (response.data.success === 1) {
            window.location = response.data.ruta;
        } else if (response.data.success === 2) {
            toastr.error('Datos incorrectos');
        } else if (response.data.success === 3) {
            toastr.error('usuario no encontrado')
        } else {
            toastr.error('error al iniciar sesión');
        }
    }


</script>
</body>

</html>
