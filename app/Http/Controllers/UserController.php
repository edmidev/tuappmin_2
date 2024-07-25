<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user')->except('get_all');
    }

    public  function index()
    {
        return view('pages.user.index');
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 1 || Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $users = User::from('users as u')
        ->join('roles as r', 'r.id', '=', 'u.rol_id')
        ->where('u.rol_id', '!=', 1)
        ->where('u.id', '!=', Auth::id())
        ->where('status', 'Activo');

        if(Auth::user()->rol_id == 1){
            $users->where('u.rol_id', 2);
        }
        else if(Auth::user()->rol_id == 2){
            $users->whereIn('u.rol_id', [3, 4])->where('u.owner_id', $owner_id);
        }
        else if(Auth::user()->rol_id == 3){
            $users->where('u.rol_id', 4)->where('u.owner_id', $owner_id);
        }

        /** Filtramos por id */
        if($request->id){
            $users->where('u.id', $request->id);
        }

        /** Filtramos por nombre */
        if($request->name){
            $users->where('u.name', 'like', '%' . $request->name . '%');
        }

        /** Filtramos por rol */
        if($request->rol_id){
            $users->where('u.rol_id', $request->rol_id);
        }

        $users->select([
            'u.*', 'r.name as rol'
        ])
        ->orderBy('u.id', 'ASC');

        $users = $users->paginate(30);

        return response()->json(compact('users'), 201);
    }

    public function get_all()
    {
        $users = User::from('users as u')
        ->where('u.rol_id', '!=', 1)
        ->where('u.id', '!=', Auth::id())
        ->select([
            'u.*'
        ])
        ->orderBy('u.id', 'ASC')
        ->get();

        return response()->json(compact('users'), 201);
    }

    public function store(Request $request)
    {
        /** Validacion de datos */
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        $data = $request->all();

        if(Auth::user()->rol_id == 2 && ($data['rol_id'] == 1 || $data['rol_id'] == 2)){
            return response()->json('No se pudo registrar el usuario.', 200);
        }

        $data['password'] = bcrypt($data['password']);

        if(Auth::user()->rol_id != 1){
            $data['owner_id'] = Auth::user()->rol_id == 2 ? Auth::id() : Auth::user()->owner_id;
        }

        if(!isset($data['rol_id'])){
            $data['rol_id'] = 2;
        }

        $data['created_by_id'] = Auth::id();
        User::create($data);

        return response()->json('Usuario creado correctamente.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        /** Validacion de datos */
        $validator = Validator::make($request->all(), [
            'email' => $request->email != $user->email ? ['required', 'string', 'email', 'max:255', 'unique:users'] : '',
            'password' => $request->password ? 'required|confirmed|min:6' : '',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->nit = $request->nit;
        $user->telefono = $request->telefono;
        $user->direccion = $request->direccion;
        $user->rol_id = $request->rol_id;
        $user->masked_phone = $request->masked_phone;

        if(isset($request->tipo)){
            $user->tipo = $request->tipo;
        }

        if($request->password){
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json('Usuario modificado correctamente.', 201);
    }

    public function change_status($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status == 'Activo' ? 'Desactivado' : 'Activo';
        $user->save();

        return response()->json('Datos modificados correctamente.', 201);
    }
}
