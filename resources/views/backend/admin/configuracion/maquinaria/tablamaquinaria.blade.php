<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Categoría</th>
        <th>Nombre</th>
        <th>Precio</th>
        <th>Descripción</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($arrayMaquinarias as $dato)
        <tr>
            <td>{{ $dato->nombreCategoria }}</td>
            <td>{{ $dato->nombre }}</td>
            <td>{{ $dato->precioFormat }}</td>
            <td>{{ $dato->descripcion }}</td>
            <td>
                <button type="button" class="btn btn-primary btn-xs" onclick="verInformacion({{ $dato->id }})">
                    <i class="fas fa-pencil-alt"></i>&nbsp; Editar
                </button>

                <button type="button" style="margin: 3px" class="btn btn-success btn-xs" onclick="vistaMantenimientos({{ $dato->id }})">
                    <i class="fas fa-file"></i>&nbsp; Mantenimientos
                </button>

                <button type="button" style="margin: 3px" class="btn btn-danger btn-xs" onclick="modalBorrar({{ $dato->id }})">
                    <i class="fas fa-trash"></i>&nbsp; Borrar
                </button>


            </td>
        </tr>
    @endforeach
    </tbody>
</table>
