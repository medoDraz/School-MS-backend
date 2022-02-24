<?php

namespace App;

//use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
//use Spatie\Translatable\HasTranslations;

class Section extends Model
{
//    use Translatable;

//    public $translatedAttributes = ['name'];
    protected $guarded=[];
//    protected $fillable=['Grade_id','Class_id'];

    protected $table = 'sections';
    public $timestamps = true;


    // علاقة بين الاقسام والصفوف لجلب اسم الصف في جدول الاقسام

    public function My_classs()
    {
        return $this->belongsTo('App\Classroom', 'Class_id');
    }
}
