<?php
/**
 * Created by PhpStorm.
 * User: n
 * Date: 2017/4/26 0026
 * Time: 15:28
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $primaryKey = 'user_id';

    public $timestamps = false;
}