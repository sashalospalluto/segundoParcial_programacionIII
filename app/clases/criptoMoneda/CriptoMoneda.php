<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CriptoMoneda extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'criptomonedas';
    public $incrementing = true;
    public $timestamps = false;

    const DELETED_AT = 'fechaBaja';

    protected $fillable = [
      'precio',	'nombre', 'nacionalidad', 'fechaBaja'	
    ];
}

?>