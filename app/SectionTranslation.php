<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SectionTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];
}
