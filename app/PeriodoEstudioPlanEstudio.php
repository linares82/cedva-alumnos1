<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PeriodoEstudioPlanEstudio extends Model
{
  
	public $table="periodo_estudio_plan_estudio";
	public $timestamps = false;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    } 

	//Mass Assignment
	protected $fillable = ['periodo_estudio_id','plan_estudio_id'];

	public function planEstudio() {
		return $this->belongsTo('App\PlanEstudio');
	}

	public function periodoEstudio() {
		return $this->belongsTo('App\PeriodoEstudio');
	}
}