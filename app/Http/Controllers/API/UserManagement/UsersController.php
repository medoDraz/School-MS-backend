<?php

namespace App\Http\Controllers\API\UserManagement;

use App\User;

//use App\Mail\ContactUs;
//use App\Notifications\InvoicePaid;
//use App\Permission_action;
//use App\Componant;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//use google\apiclient\src\Google\autoload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

//use App\Http\Requests\Admin\StoreUsersRequest;
//use App\Http\Requests\Admin\UpdateUsersRequest;
use Hash;
use Illuminate\Support\Str;

use Validator;
//use Yajra\Datatables\Datatables;
//use Google_Client;
//use Google_Service_Classroom;
//use Google_Service_Classroom_Teacher;
//use Google_Service_Classroom_Student;
use Auth;


//use Google_Service_Classroom_Course;
use Illuminate\Validation\Rule;

//define('STDIN',fopen("php://stdin","r"));
class UsersController extends Controller
{
    public function __construct()
    {
//        $this->middleware(['permission_api:Add_user'])->only('store');
//        $this->middleware(['permission_api:User_Management'])->only(['index']);
//        $this->middleware(['permission_api:Edit_user'])->only(['update', 'changestatus']);
    }

    public function index(Request $request)
    {
        $users = User::with('roles')->where(function ($q) use ($request) {

            return $q->when($request->search, function ($query) use ($request) {

                return $query->where('name', 'like', $request->search . '%')
                    ->orWhere('email', 'like', $request->search . '%');

            });

        });
//        $users = $users->whereHas('roles');
        $users = $users->latest()->paginate($request->limit);

        return response()->json(['error' => false, 'message' =>'successfully', 'data' => $users], 200);
    }

    public function getusers(Request $request)
    {
        $users = User::with('roles')->where(function ($q) use ($request) {
            return $q->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'like', $request->search . '%')
                    ->orWhere('email', 'like', $request->search . '%');
            });
        });
        $users = $users->whereHas('roles');
        $users = $users->latest()->paginate(10);

        return response()->json(['error' => false, 'message' => 'successfully', 'data' => $users], 200);
    }


    public function store(Request $request)
    {
        //dd($request);
        //dd($request->get('user')->getPermissionNames());
        $user = User::where('api_token', $request->bearerToken())->first();
        $t = ['email' => __('site.email'), 'username' => __('site.username')];
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users,email|max:55',
            //'username' => 'required|unique:users,username|max:55',
            'first_name' => 'required|max:55',
            'last_name' => 'required|max:55',
            'password' => 'required|confirmed',
        ], [], $t);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => $validator->errors()], 200);
        }
        if (!empty($request)) {
            $user = new User();
            $user->name = $request->first_name . " " . $request->last_name;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->gender = $request->gender;
            $user->status = 1;
            $user->ask_change_pass = $request->ask_change_pass;
            $user->created_by = $user->id;
            $user->api_token = Str::random(60);
            $user->password = Hash::make($request->password);
            $user->save();
            $user->assignRole($request->role);
            if ($request->privilidge == "role") {
                if (!empty($request->role)) {
                    $user->assignRole($request->role);
                }
            } elseif ($request->privilidge == "permission") {
                $perms = Permission::all();
                $perms_name = [];
                foreach ($perms as $p) {
                    array_push($perms_name, $p->name);
                }
                if (isset($request->data) && !empty($request->data)) {
                    foreach ($request->data as $k => $v) {
                        if (!empty($v)) {
                            foreach ($v as $vv) {

                                $permission_name = $k . "_" . $vv;
                                //echo "\n";
                                if (in_array($permission_name, $perms_name)) {
                                    $user->givePermissionTo($permission_name);
                                } else {
                                    $permission = Permission::create(['name' => $permission_name]);
                                    $user->givePermissionTo($permission_name);
                                }


                            }
                        }
                    }
                }
            }
        }

        return response()->json(['error' => false, 'message' => 'added_successfully', 'data' => []], 200);
    }

    public function edit(Request $request)
    {

        $user = User::where('id', $request->id)->first();
        $role = $user->roles;
        return response()->json([
            'user' => $user,
            'role' => $role[0],
        ], 200);

    }

    public function update(Request $request)
    {
//dd($request);
        $user = User::where('id', $request->id)->first();
        $t = ['email' => 'email', 'username' => 'username'];
        $validator = Validator::make($request->all(), [
            'email' => ['required', Rule::unique('users')->ignore($user->id)],
            //'username' => 'required|unique:users,username|max:55',
            'first_name' => 'required|max:55',
            //'password'=>'required|confirmed',
            'last_name' => 'required|max:55',
        ], [], $t);
        if ($validator->fails()) {
            return response()->json(['error' => true, 'message' => 'fill_all_fields', 'data' => $validator->errors()->toArray()], 200);
        }
        $user1 = User::where('api_token', $request->bearerToken())->first();

        $user->name = $request->first_name . " " . $request->last_name;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->school = $request->school;
        $user->ask_change_pass = $request->ask_change_pass;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
//        $user->password = Hash::make($request->password);
        $user->gender = $request->gender;
        $user->status = 1;
        $user->updated_by = $user1->id;
        $user->save();
        $user->syncRoles($request->role);
        $u_permissions = $user->getAllPermissions();
        foreach ($u_permissions as $p) {
            $user->revokePermissionTo($p);
        }
        $user->syncRoles($request->role);
        if ($request->privilidge == "role") {
            $u_permissions = $user->getAllPermissions();
            foreach ($u_permissions as $p) {
                $user->revokePermissionTo($p);
            }

            $user->syncRoles($request->role);
        } elseif ($request->privilidge == "permission") {
            $u_roles = $user->roles;
            foreach ($u_roles as $r) {
                $user->removeRole($r);
            }
            if (isset($request->data) && !empty($request->data)) {
                $perms = Permission::all();
                $perms_name = [];
                foreach ($perms as $p) {
                    array_push($perms_name, $p->name);
                    $user->revokePermissionTo($p);
                }

                foreach ($request->data as $k => $v) {
                    if (!empty($v)) {
                        foreach ($v as $vv) {
                            $permission_name = $k . "_" . $vv;
                            //echo "\n";
                            if (in_array($permission_name, $perms_name)) {
                                $user->givePermissionTo($permission_name);
                            } else {
                                $permission = Permission::create(['name' => $permission_name]);
                                $user->givePermissionTo($permission_name);
                            }
                        }
                    } else {

                    }
                }
            }
        }
        return response()->json(['error' => false, 'message' => 'updated_successfully', 'data' => []], 200);

    }

    public function destroy(Request $request)
    {

        $user = User::findOrFail($request->id);
        $user->delete();

        return response()->json(['error' => false, 'message' => 'deleted_successfully', 'data' => []], 200);

    }


    public function massDestroy(Request $request)
    {

        if ($request->input('ids')) {
            $entries = User::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }

    public function sentmail(Request $request)
    {

        $t = ['email' => ' email', 'subject' => ' subject', 'text' => ' content '];
        $validator = Validator::make($request->all(), [
            'subject' => 'required', 'text' => 'required'], [], $t);
        //if($validator->fails()){

        ////	return redirect("/admin/users/index/Users")->withErrors($validator)->withInput();
        //}
        $email = $request->input("email");
        $emails = explode(",", $email);
        $subject = $request->input("subject");
        $text = $request->input("text");
        //dd($request);
        Mail::to($emails)->send(new ContactUs(Auth::user()->email, $subject, $text));
        $success = " email sent successful";
        //return redirect("/admin/users/index/Users")->with('successMsg','email is sent successful .');
        return redirect()->back()->with('message', 'Email sent successful');


    }

    public function deactivate($id)
    {
        $user = User::where('id', $id)->first();
        $user->status = 0;
        $user->save();
        return "success";

    }

    public function activate($id)
    {
        $user = User::where('id', $id)->first();
        $user->status = 1;
        $user->save();
        return "success";

    }

    public function profile($id)
    {
        $user = User::where('id', $id)->first();
        return view('admin.users.profile', compact('user'));

    }

    public function updateprofile(Request $request)
    {
        $user = User::where('id', $request->userid)->first();
        //dd($request->hasFile('user_img'));
        $destinationPath = '/uploads';
        if ($request->hasFile('user_img')) {
            $md5Name = md5_file($request->file('user_img')->getRealPath());
            $guessExtension = $request->file('user_img')->guessExtension();
            $user->img = $request->file('user_img')->storeAs($destinationPath, $md5Name . '.' . $guessExtension);
            //echo " ".$md5Name." ".$guessExtension." ";
            //echo $form->student_picture;
            //exit;
            $name = $md5Name;
            $c = $guessExtension;
        }
        $user->name = $request->name;
        $user->mobile = $request->mobile;
        $user->api_token = Str::random(60);
        $user->save();
        return view('home');
    }

    public function changestatus(Request $request)
    {

        $user1 = User::where('id', $request->id)->first();
        $user = User::where('api_token', $request->bearerToken())->first();
        $user1->status = $request->status;
        $user1->updated_by = $user->id;
        $user1->save();

        return response()->json(['error' => false, 'message' => 'updated_successfully', 'data' => []], 200);

    }

    public function changepermissions(Request $request)
    {


        $user1 = User::where('id', $request->id)->first();
        $user = User::where('api_token', $request->bearerToken())->first();
        // $u_permissions = $user1->getAllPermissions();
        // foreach ($u_permissions as $p) {
        //     $user1->revokePermissionTo($p);
        // }
        //dd($u_permissions);
        $user1->syncPermissions(explode(',', $request->permissions));

        return response()->json(['error' => false, 'message' => 'updated_successfully', 'data' => []], 200);

    }
}
