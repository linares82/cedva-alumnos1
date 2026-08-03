
@extends('layouts.master1')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="page-header">
            <h1>
                {{ $planEstudios->id }} - Plan de Estudios
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>

                </small>
            </h1>
        </div>
    </div>
    <div class="col-md-8">
        <div class="profile-user-info profile-user-info-striped">
            <div class="profile-info-row">
                <div class="profile-info-name"> </div>

            </div>
        </div>
    </div>
    @if(!is_null($planEstudios))
    <div class="col-md-12">
        <table class="table  table-bordered table-hover">
            <thead>
                <th>Periodo Estudios</th><th>Seriada</th>
            </thead>
            <tbody>
                @foreach($planEstudios->periodosEstudio as $periodo)
                    <tr>
                        <th>{{ $periodo->name }}</th>
                        <td>
                        @foreach($periodo->materias as $materia)
                            <tr>
                                <td>{{ $materia->id }} - {{ $materia->name }}</td>
                                <td>@if($materia->seriada_bnd)
                                    @php
                                        //dd($materia);
                                    @endphp
                                    Sí ({{ $materia->serieAnterior->name ?? 'No disponible' }})
                                    @else
                                    No
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
