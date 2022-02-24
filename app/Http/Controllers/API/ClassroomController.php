<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Classroom;
use App\Grade;
use Illuminate\Support\Facades\DB;
use Validator;
use App\User;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{

    public function index(Request $request)
    {

        $request->limit == 'NaN' ? $limit = count(Grade::all()) : $limit = $request->limit;

        $My_Classes = Classroom::with(['grade'])->where(function ($q) use ($request) {
            return $q->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'like',  $request->search . '%');
            });
        })->when($request->grade_id, function ($query) use ($request) {
            return $query->where('grade_id', $request->grade_id);
        })->latest()->paginate($limit);
        return response()->json(['error' => false, 'message' => 'successfully', 'data' => $My_Classes], 200);

    }

    public function create()
    {

    }

    public function store(Request $request)
    {
//      dd($request);
        $t = [];
        $validator = Validator::make($request->all(), [
            'classes' => 'required',
//            'grade_id' => 'required',
        ], [], $t);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => $validator->errors()->toArray()], 200);
        }
        $user = User::where('api_token', $request->bearerToken())->first();

        $List_Classes = json_decode($request->classes);
        DB::beginTransaction();
        foreach ($List_Classes as $List_Class) {
            $class = Classroom::where('name', $List_Class->name)->where('grade_id', $List_Class->grade_id)->first();
            if ($class) {
                return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => ''], 200);
            }

            $My_Classes = new Classroom();
            $My_Classes->name =$List_Class->name;
            $My_Classes->grade_id = $List_Class->grade_id;
            $My_Classes->created_by = $user->id;
            $My_Classes->save();

        }
        DB::commit();
        $msg = "added_successfully";
        return response()->json(['error'=>false , 'message'=>$msg,'data' =>[]], 200);
//        toastr()->success(trans('site.messages.success'));
//        return redirect()->route('classrooms.index');
    }

    public function show($id)
    {

    }

    public function edit($id)
    {

    }

    public function update(Request $request)
    {
        $t = [];
        $validator = Validator::make($request->all(), [
            'classes' => 'required',
            //            'grade_id' => 'required',
        ], [], $t);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => $validator->errors()->toArray()], 200);
        }
        $user = User::where('api_token', $request->bearerToken())->first();

        $Class = json_decode($request->classes)[0];

        $My_Classes = Classroom::findOrFail($Class->id);
        $My_Classes->name = $Class->name;
        $My_Classes->grade_id = $Class->grade_id;
        $My_Classes->updated_by = $user->id;
        $My_Classes->save();

        $msg = "updated_successfully";
        return response()->json(['error'=>false , 'message'=>$msg,'data' =>[]], 200);
    }

    public function destroy(Request $request)
    {
        try {
            Classroom::findOrFail($request->id)->delete();
            toastr()->error(trans('site.messages.Delete'));
            return redirect()->route('classrooms.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

    }

    public function delete_all(Request $request)
    {
//        dd($request);
        $delete_all_id = explode(",", $request->delete_all_id);

        Classroom::whereIn('id', $delete_all_id)->Delete();
        toastr()->error(trans('site.messages.Delete'));
        return redirect()->route('classrooms.index');
    }

    public function change_status(Request $request)
    {
        $class = Classroom::where('id', $request->id)->first();
        $user = User::where('api_token', $request->bearerToken())->first();
        $class->status = $request->status;
        $class->updated_by = $user->id;
        $class->save();

        return response()->json(['error' => false, 'message' => 'updated_successfully', 'data' => []], 200);

    }

}


