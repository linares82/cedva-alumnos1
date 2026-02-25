@extends('layouts.master1')

@section('content')

<div class="box-body">
    <div class="col-md-12 alert alert-block alert-success">
    *Todos los documentos obligatorios deben ser cargados, sin excepción antes de 90 dias naturales despues de su inscripcion o se iterrumpira la captura de asistencais y calificaciones.</br>
    **La carga de los documentos opcionales seran indicados por el personal de control escolar.</br>
    ***Los archivos cargados deben ser optimizados en archivo pdf.
    </div>

    <div class="form-group col-md-12">
        <table class="table table-condensed table-striped">
            <thead>
                <tr>
                    <th>Documentos</th><th>Obligatorio</th><th>Link</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cliente->pivotDocCliente as $doc)
                <tr>
                    <td>
                        {{$doc->docAlumno->name}}
                    </td>
                    <td>
                        @if($doc->docAlumno->doc_obligatorio==1)
                        <label class="label label-success">SI</label>
                        @else
                        NO
                        @endif
                    </td>
                    <td>
                        @if(!is_null($doc->archivo))
                            @php
                                $cadena_img = explode('/', $doc->archivo);
                                $inicio_url=substr($doc->archivo, 0,4);
                            @endphp
                            @if($inicio_url<>"http")
                                <a href="{{$doc->image_url}}" class="btn btn-info btn-xs" target="_blank">Ver Archivo Cargado</a>
                            @endif
                        @endif

                            <div id="div_archivo{{ $doc->id }}">
                            <div class="btn btn-xs btn-file">
                                <i class="fa fa-paperclip"></i> Seleccionar Archivo
                                <input type="file"  id="file{{ $doc->id }}" accept=".pdf" name="file" class="cliente_archivo" >
                                <input type="hidden" name="_token" id="_token"  value="<?= csrf_token(); ?>">
                                <input type="hidden"  id="file_hidden" name="file_hidden" >
                            </div>
                            <button class="btn btn-success btn-xs btn_archivo" id="btn_archivo{{ $doc->id }}"
                                data-doc_id="{{ $doc->doc_alumno_id }}"
                                data-documento='{{ $doc->id }}'>
                                <span class="glyphicon glyphicon-ok">Guardar</span>
                            </button>
                            <br/>
                            <div id="texto_notificacion{{ $doc->id }}">

                            </div>
                            </div>


                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </br></br></br>
    </div>

</div>

@endsection

@push('scripts')
<script type="text/javascript">
$(document).on("click", ".btn_archivo", function (e) {
    e.preventDefault();

    var miurl = "{{route('alumnos.documentos.cargarImg')}}";
    // var fileup=$("#file").val();
    var divresul = "texto_notificacion"+$(this).data('documento');

    var data = new FormData();
    data.append('file', $('#file'+$(this).data('documento'))[0].files[0]);
    data.append('doc_cliente_id', $(this).data('doc_id'));
    data.append('documento', $(this).data('documento'));

    documento=$(this).data('documento');

    @if(isset($cliente))
	data.append('cliente', {{$cliente->id}});
    @endif

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('#_token').val()
        }
    });
    $.ajax({
        url: miurl,
        type: 'POST',
        // Form data
        //datos del formulario
        data: data,
        //dataType: "json",
        //necesario para subir archivos via ajax
        cache: false,
        contentType: false,
        processData: false,
        //mientras enviamos el archivo
        beforeSend: function () {
            $("#" + divresul + "").html('guardando...');
        },
        complete: function () {
            $("#" + divresul + "").html('ok');
        },
        //una vez finalizado correctamente
        success: function (data) {
            if (confirm('¿Deseas Actualizar la Página?')){
                location.reload();
            }
            $(this).text('OK');
        },
        //si ha ocurrido un error
        error: function (data) {

        }
    });
})
</script>
@endpush
