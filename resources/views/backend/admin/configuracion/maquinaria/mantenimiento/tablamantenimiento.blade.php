<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Costo</th>
        <th>Descripción</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($arrayHistorial as $dato)
        <tr>
            <td>{{ $dato->fechaFormat }}</td>
            <td>{{ $dato->precioFormat }}</td>
            <td>{{ $dato->descripcion }}</td>

            <td>
                <button type="button" class="btn btn-danger btn-xs" onclick="modalBorrar({{ $dato->id }})">
                    <i class="fas fa-trash"></i>&nbsp; Borrar
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

