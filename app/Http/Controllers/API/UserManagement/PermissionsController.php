<?php

namespace App\Http\Controllers\API\UserManagement;

use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionsRequest;
use App\Http\Requests\Admin\UpdatePermissionsRequest;
use Validator;
use App\User;
class PermissionsController extends Controller
{
    public function __construct()
    {
//        $this->middleware(['can:categories_create'])->only('create');
//        $this->middleware(['can:Settings_User_Management'])->only('index');
//        $this->middleware(['can:categories_update'])->only('edit');
//        $this->middleware(['can:categories_delete'])->only('destroy');
    }

    public function index(Request $request)
    {
        //dd($request->user_id);
        if (isset($request->user_id)) {
            $user = User::where('id', $request->user_id)->with('roles')->first();
            $permissions=$user->getAllPermissions()->pluck('name');
            $permissionsofRole=$user->getPermissionsViaRoles()->pluck('name');
            return response()->json(['error'=>false , 'message'=>__('site.successfully'),'data' =>[
                'permissions' => $permissions,
                'permissionsofRole' =>$permissionsofRole
            ]], 200);
            //dd($user->getPermissionsViaRoles());
           //$permissions = Permission::where('accountant',1)->get();
        }else{
            $permissions = Permission::all();
             return response()->json(['error'=>false , 'message'=>__('site.successfully'),'data' => $permissions], 200);
        }


    }
    public function store(Request $request)
    {
        $t = [];
        $validator = Validator::make($request->all(), ['name' => 'required|unique:permissions,name'], [], $t);
        if ($validator->fails()) {
            return response()->json(['error'=>true , 'message'=>__('site.fill_all_fields'),'data' =>[]], 200);
        }
        $user = $request->get('user');
        Permission::create([
            'name' => $request->name,
            'guard_name'=>'web',
            'created_by'=>$user->id
        ]);
        return response()->json(['error'=>false , 'message'=>__('site.added_successfully'),'data' => []], 200);
    }

    public function update(Request $request)
    {
        $t = [];
        $validator = Validator::make($request->all(), ['name' => 'required|unique:permissions,name'], [], $t);
        if ($validator->fails()) {
            return response()->json(['error'=>true , 'message'=>__('site.fill_all_fields'),'data' =>[]], 200);
        }
        $user = $request->get('user');
        $permission = Permission::findOrFail($request->id);
        $permission->update([
            'name' => $request->name,
            'guard_name'=>'web',
            'updated_by'=>$user->id
        ]);

        return response()->json(['error'=>false , 'message'=>__('site.updated_successfully'),'data' => []], 200);
    }



    public function destroy(Request $request)
    {
        $permission = Permission::findOrFail($request->id);
        $permission->delete();
        return response()->json(['error'=>false , 'message'=>__('site.deleted_successfully'),'data' => []], 200);
    }

    public function massDestroy(Request $request)
    {
        if (!Gate::allows('users_manage')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Permission::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }

}
