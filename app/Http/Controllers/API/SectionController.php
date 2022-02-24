<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Classroom;
use App\Grade;
use App\Http\Requests\SectionRequest;
use App\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{

    public function index()
    {
        $Grades = Grade::with(['Sections'])->get();

        $list_Grades = Grade::all();
        return response()->json(['error' => false, 'message' => __('site.successfully'), 'data' => $list_Grades], 200);

    }


    public function create()
    {
        //
    }


    public function store(SectionRequest $request)
    {
//        return $request;
        try {

            $validated = $request->validated();
            $Sections = new Section();

            $Sections->Name_Section = ['ar' => $request->Name_Section_Ar, 'en' => $request->Name_Section_En];
            $Sections->Grade_id = $request->Grade_id;
            $Sections->Class_id = $request->Class_id;
            $Sections->Status = 1;
            $Sections->save();
            toastr()->success(trans('site.messages.success'));

            return redirect()->route('sections.index');
        }

        catch (\Exception $e){
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function show(Section $section)
    {
        //
    }


    public function edit(Section $section)
    {
        //
    }


    public function update(SectionRequest $request )
    {
//        return $request;
        try {
            $validated = $request->validated();
            $Sections = Section::findOrFail($request->id);

            $Sections->Name_Section = ['ar' => $request->Name_Section_Ar, 'en' => $request->Name_Section_En];
            $Sections->Grade_id = $request->Grade_id;
            $Sections->Class_id = $request->Class_id;

            if(isset($request->Status)) {
                $Sections->Status = 1;
            } else {
                $Sections->Status = 2;
            }

            $Sections->save();
            toastr()->success(trans('site.messages.Update'));

            return redirect()->route('sections.index');
        }
        catch
        (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function destroy(request $request)
    {

        Section::findOrFail($request->id)->delete();
        toastr()->error(trans('site.messages.Delete'));
        return redirect()->route('sections.index');

    }

    public function getclasses($id){
        $list_classes = Classroom::where("grade_id", $id)->pluck("name_class", "id");

        return $list_classes;
    }
}
