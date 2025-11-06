<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Fecha Inicia</th>
        <th>Nombre</th>
        <th>Teléfono</th>

        <th>Membresia Actual</th>
        <th>Fecha de Salida</th>

        <th>Días Restante</th>
        <th>Estado</th>
        <th>Deuda</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($arrayClientes as $dato)
        <tr>
            <td>{{ $dato->fechaInicioMembresia }}</td>
            <td>{{ $dato->nombre }}</td>
            <td>{{ $dato->telefono }}</td>

            <td>{{ $dato->nombreMembresia }}</td>
            <td>{{ $dato->fechaSalida }}</td>

            <td>{{ $dato->diasRestantes }}</td>
            <td>
                @if ($dato->diasRestantes < 0)
                    <span class="badge bg-danger" data-toggle="tooltip" title="La membresía venció hace {{ abs($dato->diasRestantes) }} días.">Vencido</span>

                @elseif ($dato->diasRestantes === 0)
                    <span class="badge bg-gray-dark" data-toggle="tooltip" title="La membresía vence hoy.">Vence Hoy</span>

                @elseif ($dato->diasRestantes <= 5)
                    <span class="badge bg-warning text-dark" data-toggle="tooltip" title="La membresía está por vencer en {{ $dato->diasRestantes }} días.">Por vencer</span>

                @else
                    <span class="badge bg-success" data-toggle="tooltip" title="La membresía está activa">Activo</span>
                @endif
            </td>
            <td>
                @if ($dato->solvente === 1)
                    <span class="badge bg-success" data-toggle="tooltip" title="El pago de Membresia esta completa">Solvente</span>
                @else
                    <span class="badge bg-danger" data-toggle="tooltip" title="El cliente debe {{ $dato->adeudo }} para completar Pago">Adeuda: {{ $dato->adeudo }}</span>
                @endif
            </td>

            <td>
                <button type="button" class="btn btn-primary btn-xs" onclick="abrirModalInfo({{ $dato->id }})">
                    <i class="fas fa-pencil-alt"></i>&nbsp; Info
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
