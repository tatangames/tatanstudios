<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Precio</th>
        <th>Duración Días</th>
        <th>Descripción</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($arrayMembresias as $dato)
        <tr>
            <td>{{ $dato->nombre }}</td>
            <td>{{ $dato->precioFormat }}</td>
            <td>{{ $dato->duracion_dias }}</td>
            <td>{{ $dato->descripcion }}</td>
            <td>
                <button type="button" class="btn btn-primary btn-xs" onclick="verInformacion({{ $dato->id }})">
                    <i class="fas fa-pencil-alt"></i>&nbsp; Editar
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
