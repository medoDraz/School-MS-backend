<?php

namespace App;

//use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
//use Spatie\Translatable\HasTranslations;

class Classroom extends Model
{
//    use Translatable;

//    public $translatedAttributes = ['name'];
    protected $guarded=[];

    protected $table = 'classrooms';
    public $timestamps = true;
//    protected $fillable=['grade_id'];

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

}
