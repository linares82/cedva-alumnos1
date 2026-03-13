@extends('layouts.master1')

@section('content')

<style>
* {
  box-sizing: border-box;
}

.zoom {
  transition: transform .2s;
  width: auto;
  height: 250px;
  margin: 0 auto;
  z-index: 100;
}

.zoom:hover {
  -ms-transform: scale(2); /* IE 9 */
  -webkit-transform: scale(2); /* Safari 3-8 */
  transform: scale(2);
  z-index:100;
}
</style>


<div class="box-body">
    <div class="col-md-3"></div>
        <div class="zoom col-md-6" style="align:">
            <img src="{{asset('img/reglas_carga_docs.jpeg')}}" alt="instrucciones_carga_documentos" style="align: center; width: auto; height: 250px; border-radius: 4px;border: 1px solid #1165d3;">
        </div>
    <div class="col-md-3"></div>
    @if (!is_null($cliente->obs_docs) or strlen($cliente->obs_docs)>0)
    <div class="col-md-12 alert alert-block alert-danger">
        {{ $cliente->obs_docs }}
    </div>
    @endif

    <div class="form-group col-md-12">

        <table class="table table-condensed table-striped">
            <thead>
                <tr>
                    <th>Documentos</th><th>Link</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cliente->pivotDocCliente as $doc)

                @if($doc->docAlumno->bnd_portal_alumnos==1)
                @php
                    //dd($doc->docAlumno);
                @endphp
                <tr>
                    <td>
                        {{$doc->docAlumno->name}}
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
                            @if($doc->docAlumno->bnd_pdf==1)
                                <div class="btn btn-xs btn-file">
                                    <i class="fa fa-paperclip"></i> Seleccionar Archivo PDF
                                    <input type="file"  id="file{{ $doc->id }}"
                                    accept=".pdf"
                                    name="file" class="cliente_archivo" >
                                    <input type="hidden" name="_token" id="_token"  value="<?= csrf_token(); ?>">
                                    <input type="hidden"  id="file_hidden" name="file_hidden" >
                                </div>
                            @elseif($doc->docAlumno->bnd_imagen==1)
                                <div class="btn btn-xs btn-file">
                                    <i class="fa fa-paperclip"></i> Seleccionar Archivo Imagen
                                    <input type="file"  id="file{{ $doc->id }}"
                                    accept="image/jpg, image/jpeg"
                                    name="file" class="cliente_archivo" >
                                    <input type="hidden" name="_token" id="_token"  value="<?= csrf_token(); ?>">
                                    <input type="hidden"  id="file_hidden" name="file_hidden" >
                                </div>
                            @else
                                <div class="btn btn-xs btn-file">
                                    <i class="fa fa-paperclip"></i> Seleccionar Archivo
                                    <input type="file"  id="file{{ $doc->id }}"
                                    name="file" class="cliente_archivo" >
                                    <input type="hidden" name="_token" id="_token"  value="<?= csrf_token(); ?>">
                                    <input type="hidden"  id="file_hidden" name="file_hidden" >
                                </div>
                            @endif

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
                @endif
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
            if (confirm('Â¿Deseas Actualizar la PÃ¡gina?')){
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
