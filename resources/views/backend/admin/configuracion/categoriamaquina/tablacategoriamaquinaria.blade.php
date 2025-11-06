<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th style="width: 60%">Nombre</th>
        <th style="width: 8%">Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($arrayCategorias as $dato)
        <tr>
            <td>{{ $dato->nombre }}</td>
            <td>
                <button type="button" class="btn btn-primary btn-xs" onclick="verInformacion({{ $dato->id }})">
                    <i class="fas fa-pencil-alt"></i>&nbsp; Editar
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
