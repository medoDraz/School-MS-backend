<?php

namespace App\Http\Controllers\API\UserManagement;

//use App\Componant;
//use App\School;
use App\User;
use Validator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Validation\Rule;

class RolesController extends Controller
{

    public function __construct()
    {
//        $this->middleware(['permission_api:Add_role'])->only('store');
//        $this->middleware(['permission_api:User_Management'])->only(['index']);
//        $this->middleware(['permission_api:Edit_role'])->only(['update','changestatus']);
    }

    public function index(Request $request)
    {
        $role = Role::where(function ($q) use ($request) {
            return $q->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'like', $request->search . '%');
            });
        })->latest()->paginate($request->limit);
        return response()->json(['error' => false, 'message' => 'successfully', 'data' => $role], 200);
    }

    public function getallroles(Request $request)
    {
        $role = Role::where(function ($q) use ($request) {
            return $q->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->search . '%');
            });
        })->latest()->get();
        return response()->json(['error' => false, 'message' => 'successfully', 'data' => $role], 200);
    }

    public function store(Request $request)
    {
         //dd($request);
        $t = ['name' => __('site.role_name')];
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
//            'focus' => 'required'
        ], [], $t);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => $validator->errors()], 200);
        }
        $perms = Permission::all();
        $perms_name = [];
        foreach ($perms as $p) {
            array_push($perms_name, $p->name);
        }

        $user = User::where('api_token', $request->bearerToken())->first();
        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            //'status' => $request->status,
//            'focus' => $request->focus,
            'status' => 1,
            'created_by' => $user->id
        ]);
        //dd(explode(',', $request->permissions));
        foreach (explode(',', $request->permissions) as $perm_name){
            if (in_array($perm_name, $perms_name)){
                $role->givePermissionTo($perm_name);
            }else {
                $permission = Permission::create(['name' => $perm_name]);
                $role->givePermissionTo($perm_name);
            }
        }
        return response()->json(['error'=>false , 'message'=>'added_successfully','data' => []], 200);
    }

	public function edit(Request $request){
		//dd($request);
		$role = Role::where('id', $request->id)->first();
		//dd($role->getAllPermissions()->pluck('name'));
		return response()->json([
			'role'=>$role,
//            'focus'=>explode(',', $role->focus),
            'permissions'=>$role->getAllPermissions()->pluck('name'),

		], 200);

	}

    public function update(Request $request)
    {
        //dd(explode(',', $request->permissions));
        $role = Role::findOrFail($request->id);
		$t = ['name' => __('site.role_name')];
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('roles')->ignore($role->id )],
//            'focus' => 'required'
        ], [], $t);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => $validator->fails()], 200);
        }
        $perms = Permission::all();
        $perms_name = [];
        foreach ($perms as $p) {
            array_push($perms_name, $p->name);
            $role->revokePermissionTo($p);
        }
        $user = User::where('api_token', $request->bearerToken())->first();
        $role->name = $request->name;
        $role->description = $request->description;
        $role->updated_by = $user->id;
        $role->save();


        foreach (explode(',', $request->permissions) as $perm_name){
            if (in_array($perm_name, $perms_name)){
                $role->givePermissionTo($perm_name);
            }
			else{
				$permission = Permission::create(['name' => $perm_name]);
                $role->givePermissionTo($perm_name);
			}
        }
        return response()->json(['error'=>false , 'message'=>'updated_successfully','data' => []], 200);
    }

    public function destroy(Request $request)
    {

        $role = Role::findOrFail($request->id);
        $role->delete();

        return response()->json(['error'=>false , 'message'=>'deleted_successfully','data' => []], 200);
    }


    public function massDestroy(Request $request)
    {

        if ($request->input('ids')) {
            $entries = Role::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }

	public function changestatus(Request $request)
    {
        $role = Role::where('id', $request->id)->first();
		$user = User::where('api_token', $request->bearerToken())->first();
        $role->update([
            'status' => $request->status,
            'updated_by' => $user->id,
        ]);

        return response()->json(['error' => false, 'message' => 'updated_successfully', 'data' => []], 200);

    }

    public function deactivate($id)
    {
        $role = Role::where('id', $id)->first();
        $role->status = 0;
        $role->save();
        return "success";

    }

    public function activate($id)
    {
        $role = Role::where('id', $id)->first();
        $role->status = 1;
        $role->save();
        return "success";

    }

}
