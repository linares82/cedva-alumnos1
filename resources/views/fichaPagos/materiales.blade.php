
@extends('layouts.master1')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="page-header">
            <h1>
                Materiales
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>

                </small>
            </h1>
        </div>
    </div>
    <div class="col-md-8">
        <div class="profile-user-info profile-user-info-striped">
            <div class="profile-info-row">
                <div class="profile-info-name"> Materiales </div>
                <div class="profile-info-value">
                    Facilitamos una lista de los materiales o recursos para tus clases.
                </div>
            </div>
        </div>
    </div>
    @if(!is_null($materiales))
    <div class="col-md-12">
        <table class="table  table-bordered table-hover">
            <thead>
                <th>Descripcion</th><th>Archivo</th>
            </thead>
            <tbody>
                @foreach($materiales as $material)
                <tr>
                    <td>
                        {{ $material->descripcion }}
                    </td>
                    <td>
                        <a href="{{ $material->archivoUrl }}" target="_blank">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
