<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

//use App\Classroom;
use Validator;
//use App\Http\Requests\GradeRequest;
use App\Grade;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{

    public function index(Request $request)
    {
        $request->limit == 'NaN' ? $limit = count(Grade::all()) : $limit = $request->limit;

        $Grades = Grade::with([])->where(function ($q) use ($request) {
            return $q->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'like', $request->search . '%');
            });
        })->latest()->paginate($limit);
        return response()->json(['error' => false, 'message' => 'successfully', 'data' => $Grades], 200);
    }

    public function store(Request $request)
    {
        $t = [];
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:grades',
        ], [], $t);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => $validator->errors()->toArray()], 200);
        }
        $user = User::where('api_token', $request->bearerToken())->first();

        $grade = new  Grade();
        $grade->name = $request->name;
        $grade->notes = $request->note;
        $grade->created_by = $user->id;
        $grade->save();
        return response()->json(['error' => false, 'message' => 'added_successfully', 'data' => []], 200);

    }

    public function update(Request $request)
    {
        $t = [];
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:grades',
        ], [], $t);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => $validator->errors()->toArray()], 200);
        }
        $user = User::where('api_token', $request->bearerToken())->first();

        $grade = Grade::where('id', $request->id)->first();
        $grade->name = $request->name;
        $grade->notes = $request->note;
        $grade->updated_by = $user->id;
        $grade->save();
        return response()->json(['error' => false, 'message' => 'updated_successfully', 'data' => []], 200);

    }

    public function change_status(Request $request)
    {
        $grade = Grade::where('id', $request->id)->first();
        $user = User::where('api_token', $request->bearerToken())->first();
        $grade->status = $request->status;
        $grade->updated_by = $user->id;
        $grade->save();

        return response()->json(['error' => false, 'message' => 'updated_successfully', 'data' => []], 200);

    }

    public function destroy(Request $request)
    {
        try {
            $my_classes = Classroom::where('grade_id', $request->id)->pluck('grade_id');
            if ($my_classes->count() == 0) {
                Grade::findOrFail($request->id)->delete();
                toastr()->error(trans('site.messages.Delete'));
                return redirect()->route('grade.index');
            } else {
                toastr()->error(trans('site.Classes_trans.delete_Class_Error'));
                return redirect()->route('grade.index');
            }

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}


