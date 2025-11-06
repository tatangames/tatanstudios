<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th data-fecha-eu style="width: 12%">Fecha Cumpleaños</th>
        <th>Nombre</th>
        <th>Teléfono</th>
        <th>Correo</th>
        <th>Sexo</th>
        <th>Edad</th>
    </tr>
    </thead>
    <tbody>
    @foreach($arrayClientes as $dato)
        <tr>
            <td>{{ $dato->fechaNacimiento }}</td>
            <td>{{ $dato->nombre }}</td>
            <td>{{ $dato->telefono }}</td>
            <td>{{ $dato->correo }}</td>
            <td>{{ $dato->genero }}</td>
            <td>{{ $dato->edad }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

