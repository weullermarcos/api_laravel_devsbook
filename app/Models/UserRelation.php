<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRelation extends Model
{
    use HasFactory;

    public $timestamps = false;
    //especifica o nome da tabela que está no banco de dados
    protected $table = 'userrelations';
}
