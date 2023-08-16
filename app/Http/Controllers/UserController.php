<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $name = $request->input('name');
            $roleName = $request->input('role');
            $id = $request->input('id');
            $usersQuery = User::query();
            if ($id) {
                $usersQuery->where('id', $id);
            }
            if ($name) {
                $usersQuery->where('name', 'LIKE', "%$name%");
            }

            if ($roleName) {
                $role = Role::where('name', $roleName)->first();

                if ($role) {
                    $usersQuery->whereHas('roles', function ($query) use ($role) {
                        $query->where('id', $role->id);
                    });
                }
            }

            $users = $usersQuery->get();
            return $this->responseSuccess('Successfully Get User', $users, 200);
        } catch (\Exception $e) {
            return $this->responseFailed('Unexpected Error', '', 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function assignRoleUser(Request $request, $id)
    {
        $input = $request->all();
        try {
            $user = User::where('id', $id)->with('roles')->first();
            $user->syncRoles([]);
            if (!empty($input['role'])) {
                $user->assignRole($input['role']);
            }
            return $this->responseSuccess('User role assigned', $user, 200);
        } catch (\Exception $e) {
            return $this->responseFailed('Failed', null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|between:8,255',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        try {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => bcrypt($input['password']),
            ]);

            if (!empty($input['role'])) {
                $user->assignRole($input['role']);
            }

            $data = User::where('id', $user->id)->with('roles')->first();

            return $this->responseSuccess('User created Successfully', $data, 201);
        } catch (\Exception $e) {
            return $this->responseFailed('Failed', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('Data not found', '', 404);

        $data = User::where('id', $id)->with(['roles'])->first();
        return $this->responseSuccess('User detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('Data not found', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string',
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error validation', $validator->errors(), 400);
        }

        $user->syncRoles([]);

        if (!empty($input['role'])) {
            $user->assignRole($input['role']);
        }

        try {
            $user->update([
                'name' => $input['name'],
                'email' => $input['email']
            ]);

            $data = User::where('id', $id)->with('roles')->first();
            return $this->responseSuccess('User updated successfully', $data, 200);
        } catch (\Exception $e) {
            return $this->responseFailed('Failed', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('Data not found', '', 404);
        try {
            $user->delete();
            return $this->responseSuccess('Delete Successful');
        } catch (\Exception $e) {
            return $this->responseFailed('Unexpected Error', '', 500);
        }
    }
}
