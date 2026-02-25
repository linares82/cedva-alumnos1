<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Auth;
use File;
use Excel;
use App\Sm;
use Session;
use App\Caja;
use App\User;
use App\Aviso;
use App\Grado;
use App\Grupo;
use App\Medio;
use App\Nivel;
use App\Paise;
use App\Param;
use App\Adeudo;
use App\Correo;
use App\Estado;
use App\Cliente;
use App\Lectivo;
use App\Plantel;
use App\Prebeca;
use App\Empleado;
use App\Materium;
use App\DocAlumno;
use App\Municipio;
use App\Plantilla;
use App\Preguntum;
use App\StCliente;
use Carbon\Carbon;
use App\Hacademica;
use App\MotivoBeca;
use App\PlanPagoLn;
use App\TpoInforme;
use App\EstadoCivil;
use App\Inscripcion;
use App\Seguimiento;
use App\StProspecto;
use App\AvisosInicio;
use App\CajaConcepto;
use App\Especialidad;
use App\Rules\IsCurp;
use App\SeccionesCat;
use App\AlumnosActivo;
use App\Ccuestionario;
use App\StSeguimiento;
use App\PorcentajeBeca;
use App\UsuarioCliente;
use Twilio\Rest\Client;
use App\HistoriaCliente;
use App\PivotDocCliente;
use App\PreguntaCliente;
use App\IncidenceCliente;
use App\CcuestionarioDato;
use App\ProcedenciaAlumno;
use App\CombinacionCliente;
use App\Helpers\ValidaCurp;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Requests\Carga;
use Illuminate\Http\Request;
use App\ConsecutivoMatricula;
use App\ConsultaCalificacion;
use App\ImpresionComprobanteE;
use App\SepTEstudioAntecedente;
use App\Http\Controllers\Controller;
use App\Http\Requests\createCliente;
use App\Http\Requests\updateCliente;
use App\Services\UploadService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class ClientesController extends Controller
{




    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(Cliente $cliente)
    {
        //dd($cliente);


        $doc_existentes = DB::table('pivot_doc_clientes as pde')->select('doc_alumno_id')
            ->join('clientes as c', 'c.id', '=', 'pde.cliente_id')
            ->where('c.id', '=', $cliente->id)
            ->where('pde.deleted_at', '=', null)->get();
        if (count($doc_existentes) == 0) {
            $datos['cliente_id'] = $cliente->id;
            $this->crearListaCheck($datos);
        }

        $de_array = array();
        if ($doc_existentes->isNotEmpty()) {
            foreach ($doc_existentes as $de) {
                array_push($de_array, $de->doc_alumno_id);
            }
            //dd($de_array);
        }

        $documentos_faltantes = DB::table('doc_alumnos')
            ->select()
            ->whereNotIn('id', $de_array)
            ->get();
        //dd($cliente->toArray());

        return view('clientes.docAlumnos', compact(
            'cliente',
            'documentos_faltantes',
        ));
    }

    public function cargarImg(Request $request)
    {
        $r = $request->hasFile('file');
        $datos = $request->all();

        $documento = PivotDocCliente::find($datos['documento']);
        //dd($documento);
        //Se borra el anterior archivo si existe
        //dd(!is_null($documento->archivo));
        if (!is_null($documento->archivo)) {
            UploadService::delete($datos['cliente'] . "/" . $documento->archivo, "do_doc_alumnos");
        }

        //Secuarda el nuevo archivo
        $image = UploadService::upload(data_get($datos, 'file'), $datos['cliente'] . "/", 'do_doc_alumnos');

        //Se actuaizan datos
        $documento->cliente_id = $datos['cliente'];
        $documento->doc_alumno_id = $datos['doc_cliente_id'];
        $documento->archivo = $image;
        //$documento->usu_alta_id = Auth::user()->id;
        $documento->usu_mod_id = 1;
        $documento->save();

        return $documento;

        //dd($image);
        /*if ($r) {
            $logo_file = $request->file('file');
            $input['file'] = $logo_file->getClientOriginalName();
            $ruta_web = asset("/imagenes/clientes/" . $datos['cliente']);
            //dd($ruta_web);
            $ruta = public_path() . "/imagenes/clientes/" . $datos['cliente'] . "/";
            if (!file_exists($ruta)) {
                File::makedirectory($ruta, 0777, true, true);
            }
            if ($request->file('file')->move($ruta, $input['file'])) {
                $documento = PivotDocCliente::find($datos['documento']);
                $documento->cliente_id = $datos['cliente'];
                $documento->doc_alumno_id = $datos['doc_cliente_id'];
                $documento->archivo = $ruta_web . "/" . $input['file'];
                //$documento->usu_alta_id = Auth::user()->id;
                $documento->usu_mod_id = 1;
                $documento->save();

                //$this->docObligatoriosEntregados($documento->cliente_id);

                echo json_encode($ruta_web . "/" . $input['file']);
            } else {
                echo json_encode(0);
            }
        }*/
        //echo json_encode(0);
    }

    public function destroy(PivotDocCliente $pivotDocCliente)
    {
        //$pivotDocCliente = $pivotDocCliente->find($id);
        $cliente = $pivotDocCliente->cliente_id;
        $pivotDocCliente->delete();
        //$this->docObligatoriosEntregados($cliente);


        return redirect()->route('clientes.edit', $cliente)->with('message', 'Registro Borrado.');
    }



    public function crearListaCheck($datos)
    {
        $documentos = DocAlumno::get();
        foreach ($documentos as $doc) {
            $buscarRegistro = PivotDocCliente::where('cliente_id', $datos['cliente_id'])->where('doc_alumno_id', $doc->id)->first();
            if (is_null($buscarRegistro)) {
                $input['doc_alumno_id'] = $doc->id;
                $input['cliente_id'] = $datos['cliente_id'];
                $input['usu_alta_id'] = 1;
                $input['usu_mod_id'] = 1;
                PivotDocCliente::create($input);
            }
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param Request $request
     * @return Response
     */
    public function update($id, Cliente $cliente, updateCliente $request)
    {

        //$input = $request->all();
        //dd($request->all());
        $input_procedencia = $request->only(
            'institucion_procedencia',
            'sep_t_estudio_antecedente_id',
            'estado_procedencia_id',
            'fecha_inicio',
            'fecha_terminacion',
            'numero_cedula'
        );
        $input_procedencia['estado_id'] = $input_procedencia['estado_procedencia_id'];
        $procedenciaAlumno = ProcedenciaAlumno::where('cliente_id', $id)->first();
        $procedenciaAlumno->update($input_procedencia);

        $input_prebeca = $request->only('motivo_beca_id', 'porcentaje_beca_id', 'obs_prebeca');
        $prebeca = Prebeca::where('cliente_id', $id)->first();
        //dd($prebeca);
        $prebeca->update($input_prebeca);

        $input = $request->except([
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12',
            '13',
            '14',
            '15',
            '16',
            '17',
            '18',
            '19',
            '20',
            '21',
            '22',
            '23',
            '24',
            '25',
            '26',
            '27',
            '28',
            '29',
            '30',
            '31',
            '32',
            '33',
            '34',
            '35',
            '36',
            '37',
            '38',
            '39',
            '40',
        ]);
        //dd($input);
        $preguntas = $request->only([
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12',
            '13',
            '14',
            '15',
            '16',
            '17',
            '18',
            '19',
            '20',
            '21',
            '22',
            '23',
            '24',
            '25',
            '26',
            '27',
            '28',
            '29',
            '30',
            '31',
            '32',
            '33',
            '34',
            '35',
            '36',
            '37',
            '38',
            '39',
            '40',
        ]);
        //dd($preguntas);
        $input['usu_mod_id'] = Auth::user()->id;
        //dd($input);
        if (is_null($input['ape_materno'])) {
            $input['ape_materno'] = " ";
        }
        if (is_null($input['nombre2'])) {
            $input['nombre2'] = " ";
        }

        //$empleado=Empleado::find($request->input('empleado_id'));
        //$input['plantel_id']=$empleado->plantel->id;
        /*
        $pc['cliente_id'] = $id;
        $pc['pregunta_id'] = $input['pregunta_id'];
        $pc['respuesta'] = $input['respuesta'];
        $pc['usu_alta_id'] = Auth::user()->id;
        $pc['usu_mod_id'] = Auth::user()->id;
         */
        //dd($pc);
        unset($input['pregunta_id']);
        unset($input['respuesta']);
        //dd($input);
        if (!isset($input['promociones'])) {
            $input['promociones'] = 0;
        } else {
            $input['promociones'] = 1;
        }
        if (!isset($input['promo_cel'])) {
            $input['promo_cel'] = 0;
        } else {
            $input['promo_cel'] = 1;
        }
        if (!isset($input['promo_correo'])) {
            $input['promo_correo'] = 0;
        } else {
            $input['promo_correo'] = 1;
        }
        if (!isset($input['celular_confirmado'])) {
            $input['celular_confirmado'] = 0;
        } else {
            $input['celular_confirmado'] = 1;
        }
        if (!isset($input['bnd_trabaja'])) {
            $input['bnd_trabaja'] = 0;
        } else {
            $input['bnd_trabaja'] = 1;
        }
        if (!isset($input['bnd_indigena'])) {
            $input['bnd_indigena'] = 0;
        } else {
            $input['bnd_indigena'] = 1;
        }
        if (!isset($input['extranjero'])) {
            $input['extranjero'] = 0;
        } else {
            $input['extranjero'] = 1;
        }


        if (!isset($input['bnd_beca'])) {
            $input['bnd_beca'] = 0;
        } else {
            $input['bnd_beca'] = 1;
        }

        if (!isset($input['bnd_regingreso'])) {
            $input['bnd_regingreso'] = 0;
        } else {
            $input['bnd_regingreso'] = 1;
        }

        if (!is_null($input['abreviatura_estado'])) {
            $estado = Estado::where('abreviatura', $input['abreviatura_estado'])->first();
            $input['estado_nacimiento_id'] = $estado->id;
        }


        //dd($input);
        //update data
        $cliente = $cliente->find($id);
        $cantidad_preguntas = 0;
        if ($cliente->ccuestionario_id > 0) {
            $cantidad_preguntas = $cliente->ccuestionario->ccuestionarioPreguntas->count();
        }

        //dd($input);
        $cliente->update($input);

        $usuarioCliente = UsuarioCliente::where('name', $cliente->matricula)->first();
        if (!is_null($usuarioCliente)) {
            $usuarioCliente->email = $cliente->mail;
            $usuarioCliente->save();
        }


        //dd($request->all());
        if ($request->has('doc_cliente_id') and $request->input('doc_cliente_id') != '0' and $request->has('archivo')) {
            $input2['doc_alumno_id'] = $request->get('doc_cliente_id');
            $input2['archivo'] = $request->get('archivo');
            $input2['cliente_id'] = $id;
            $input2['usu_alta_id'] = Auth::user()->id;
            $input2['usu_mod_id'] = Auth::user()->id;
            PivotDocCliente::create($input2);
        }

        foreach ($preguntas as $llave => $valor) {
            if ($llave != '_token' and !is_null($valor)) {
                //dd($preguntas);
                $dato = CcuestionarioDato::where('cliente_id', '=', $id)
                    ->where('ccuestionario_id', '=', $input['ccuestionario_id'])
                    ->where('ccuestionario_pregunta_id', '=', $llave)
                    ->first();
                //dd($dato);
                if (is_null($dato)) {
                    $r = new CcuestionarioDato;
                    $r->ccuestionario_id = $input['ccuestionario_id'];
                    $r->cliente_id = $id;
                    $r->ccuestionario_id = $input['ccuestionario_id'];
                    $r->ccuestionario_pregunta_id = $llave;
                    $r->ccuestionario_respuesta_id = $valor;
                    $r->name = "";
                    $r->clave = "";
                    $r->usu_alta_id = Auth::user()->id;
                    $r->usu_mod_id = Auth::user()->id;
                    //dd($r->toArray());
                    $r->save();
                } else {
                    $dato->ccuestionario_respuesta_id = $valor;
                    $dato->name = "";
                    $dato->usu_alta_id = Auth::user()->id;
                    $dato->usu_mod_id = Auth::user()->id;
                    //dd($dato);
                    $dato->save();
                }
            }
        }
        $cantidad_respuestas = CcuestionarioDato::where('cliente_id', '=', $id)
            ->where('ccuestionario_id', '=', $input['ccuestionario_id'])
            ->count();
        if ($cantidad_preguntas != $cantidad_respuestas) {
            return redirect()->route('clientes.edit', $id)->with('message', 'Cuestionario incompleto.');
        }

        return redirect()->route('clientes.edit', $id)->with('message', 'Registro Actualizado.');
    }
}
