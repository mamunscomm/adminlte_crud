<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rows = User::paginate(20);

        return view('users.index', compact('rows'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        if (Session::has('error')) return redirect()->back()->with('error', Session::get('error'));
        $user = User::create($request->all());
        if (!$user) return redirect()->back()->with('error', "Error to create currency, please try again");

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $row = User::findOrFail($id);

        return view('users.edit', compact('row'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        if (Session::has('error')) return redirect()->back()->with('error', Session::get('error'));

        $user = User::findOrFail($id);

        $data = [];

        if(isset($request->name)){
            $data['name'] = $request->name;
        }

        if(isset($request->email)){
            $data['email'] = $request->email;
        }

        if(isset($request->password)){
            $data['password'] = Hash::make($request->password);
        }

        $update = $user->update($data);

        if (!$update) return redirect()->back()->with('error', "Error to update user, please try again");

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $delete = $user->delete();

        if (!$delete) return redirect()->back()->with('error', "Error to delete user, please try again");

        return redirect()->route('users.index');
    }
}
