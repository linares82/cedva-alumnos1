<?php

namespace App;

use App\Services\UploadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PivotDocCliente extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = array())
    {

        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = ['cliente_id', 'doc_alumno_id', 'archivo', 'usu_alta_id', 'usu_mod_id', 'doc_entregado'];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end

    public function cliente()
    {
        return $this->belongsTo('App\Cliente');
    } // end
    public function docAlumno()
    {
        return $this->belongsTo('App\DocAlumno');
    } // end

    public function getImageUrlAttribute()
    {
        return UploadService::urlFile($this->cliente_id . "/" . $this->archivo, "do_doc_alumnos");
    }


    protected $dates = ['deleted_at'];
}
