<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Fecha Inicio</th>
        <th>Fecha Finaliza</th>
        <th>Nombre Membresia</th>
        <th>Precio</th>
        <th>Solvente</th>
        <th>Duración Dias</th>
        <th>Ultima Membresia</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($arrayHistorial as $dato)
        <tr>
            <td>{{ $dato->fechaInicio }}</td>
            <td>{{ $dato->fechaFin }}</td>
            <td>{{ $dato->nombre }}</td>
            <td>{{ $dato->precioFormat }}</td>
            <td>
                @if ($dato->solvente === 1)
                    <span class="badge bg-success">Sí</span>
                @else
                    <span class="badge bg-danger">No</span>
                @endif
            </td>
            <td>{{ $dato->duracion_dias }}</td>
            <td>
                @if ($dato->is_actual === 1)
                    <span class="badge bg-success">Este</span>
                @endif
            </td>

            <td>
                <button type="button" class="btn btn-primary btn-xs" onclick="verInformacion({{ $dato->id }})">
                    <i class="fas fa-pencil-alt"></i>&nbsp; Editar
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

