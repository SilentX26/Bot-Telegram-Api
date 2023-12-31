<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuisines extends Model
{
    use HasFactory;

    protected $table = 'cuisines';
    protected $guarded = ['id'];
    public $timestamps = true;
}
