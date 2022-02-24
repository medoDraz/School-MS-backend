<?php

namespace App;

//use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
//use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Spatie\Translatable\HasTranslations;


class Grade extends Model
{
//    use Translatable;

//    public $translatedAttributes = ['name'];
    protected $guarded=[];
    protected $table = 'grades';
    public $timestamps = true;

    use SoftDeletes;
//    public $translatable = ['name'];
    protected $dates = ['deleted_at'];

    public function Sections()
    {
        return $this->hasMany(Section::class, 'Grade_id');
    }


}
