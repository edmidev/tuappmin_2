<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;
use App\Models\ResidenteConjunto;
use App\Models\Apartamento;
use App\Models\Casa;
use App\Models\PhoneUser;

class ResidenteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:1,2,3')->only([
            'get_all_paginate', 'index', 'get_all'
        ]);
        $this->middleware('permisosRoles:2,3')->only([
            'store', 'update', 'destroy'
        ]);
    }

    public function index()
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        if($owner_id){
            $data['residencia'] = User::whereId($owner_id)->select('tipo')->first();
        }
        else{
            $residencia = new \stdClass;
            $residencia->tipo = null;
            $data['residencia'] = json_encode($residencia);
        }

        return view('pages.residente.index', compact('data'));
    }

    public function get_all(Request $request)
    {
        /** Obtenemos el propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        $users = ResidenteConjunto::join('users as u', 'u.id', '=', 'residentes_conjuntos.user_id')
        ->join('users as us', 'us.id', '=', 'residentes_conjuntos.conjunto_id')
        ->where('u.rol_id', 5);

        if(Auth::user()->rol_id == 2 || Auth::user()->rol_id == 3){
            $users->where('residentes_conjuntos.conjunto_id', $owner_id);
        }

        $users->select([
            'u.name', 'residentes_conjuntos.id'
        ])
        ->orderBy('residentes_conjuntos.id', 'DESC');

        $users = $users->get();

        return response()->json(compact('users'), 201);
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos el propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        $users = ResidenteConjunto::join('users as u', 'u.id', '=', 'residentes_conjuntos.user_id')
        ->join('users as us', 'us.id', '=', 'residentes_conjuntos.conjunto_id')
        ->leftJoin('casas as c', 'c.id', '=', 'residentes_conjuntos.casa_id')
        ->leftJoin('apartamentos as a', 'a.id', '=', 'residentes_conjuntos.apartamento_id')
        ->where('u.rol_id', 5);

        if(Auth::user()->rol_id == 2 || Auth::user()->rol_id == 3){
            $users->where('residentes_conjuntos.conjunto_id', $owner_id);
        }

        /** Filtramos por id */
        if($request->id){
            $users->where('u.id', $request->id);
        }

        /** Filtramos por nombre */
        if($request->name){
            $users->where('u.name', 'like', '%' . $request->name . '%');
        }

        $users->select([
            'residentes_conjuntos.*', 'u.name', 'us.name as residencia',
            'u.email', 'u.telefono', 'c.numero', 'a.apartamento', 'a.bloque'
        ])
        ->orderBy('residentes_conjuntos.id', 'DESC');

        $users = $users->paginate(30);

        foreach ($users as $user) {
            $user->phones = PhoneUser::where('user_id', $user->user_id)->get();
        }

        return response()->json(compact('users'), 201);
    }

    public function store(Request $request)
    {
        /** Obtenemos el propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        /** Verificamos que no exista ningun residente en este departamento */
        $residente_conjunto = ResidenteConjunto::where('conjunto_id', $owner_id);

        $message = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $residente_conjunto->where('apartamento_id', $request->apartamento_id);
            $message = 'Este apartamento ya se encuentra ocupado';
        }
        else{
            $residente_conjunto->where('casa_id', $request->casa_id);
            $message = 'Esta casa ya se encuentra ocupada.';
        }

        $residente_conjunto = $residente_conjunto->first();

        if($residente_conjunto){
            return response()->json($message, 203);
        }

        /** Verificamos si el email ya esta registrado */
        $user = User::where('email', $request->email)->first();

        if($user && $user->rol_id != 5){
            return response()->json('Este email ya se encuentra registrado', 203);
        }

        if($user && !$request->save_force){
            return response()->json('Email existente', 202);
        }

        /** Validacion de datos */
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => !$request->save_force ? ['required', 'string', 'email', 'max:255', 'unique:users'] : [],
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        if(!$request->save_force){
            $user = $request->except([
                'casa_id', 'apartamento_id',
                'nombre_propietario', 'info_telefono'
            ]);

            /** Encriptamos la contraseña */
            $user['password'] = bcrypt($user['password']);

            $user['created_by_id'] = Auth::id();
            $user['rol_id'] = 5;

            /** Creamos el usuario */
            $user = User::create($user);

            foreach ($request->phones as $phone) {
                if ( (!$phone['number'] && !$phone['id'])
                    || ($phone['deleted'] && !$phone['id'])) {
                    continue;
                }

                $phoneUser = ($phone['id']) ? PhoneUser::find($phone['id']) : new PhoneUser();
                if($phone['deleted']) {
                    $phoneUser->delete();
                } else {
                    $phoneUser->phone_number = $phone['number'];
                    $phoneUser->user_id = $user->id;
                    $phoneUser->save();
                }
            }
        }

        /** Asociamos y guardamos el residente con la residencia */
        $residente_conjunto = new ResidenteConjunto;
        $residente_conjunto->apartamento_id = $request->apartamento_id;
        $residente_conjunto->casa_id = $request->casa_id;
        $residente_conjunto->user_id = $user->id;
        $residente_conjunto->conjunto_id = $owner_id;
        $residente_conjunto->created_by_id = Auth::id();
        $residente_conjunto->save();

        return response()->json('Residente creado correctamente.', 201);
    }

    public function update(Request $request, $id)
    {
        /** Obtenemos el propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        /** Verificamos que no exista ningun residente en este departamento */
        $residente_conjunto_verify = ResidenteConjunto::where('conjunto_id', $owner_id);
        $residente_conjunto = ResidenteConjunto::find($id);
        $message = '';

        $update_residencia = false;
        if($conjunto_residencial->tipo == 'Apartamento'){
            $residente_conjunto_verify->where('apartamento_id', $request->apartamento_id);
            $message = 'Este apartamento ya se encuentra ocupado';

            if($residente_conjunto->apartamento_id != $request->apartamento_id){
                $update_residencia = true;
            }
        }
        else{
            $residente_conjunto_verify->where('casa_id', $request->casa_id);
            $message = 'Esta casa ya se encuentra ocupada.';

            if($residente_conjunto->casa_id != $request->casa_id){
                $update_residencia = true;
            }
        }

        $residente_conjunto_verify = $residente_conjunto_verify->first();

        if($residente_conjunto_verify && $update_residencia){
            return response()->json($message, 203);
        }

        //$residente_conjunto->apartamento_id = $request->apartamento_id;
        //$residente_conjunto->casa_id = $request->casa_id;
        $residente_conjunto->save();

        $user = User::findOrFail($residente_conjunto->user_id);

        /** Validacion de datos */
        $validator = Validator::make($request->all(), [
            'email' => $request->email != $user->email ? ['required', 'string', 'email', 'max:255', 'unique:users'] : '',
            'password' => $request->password ? 'required|confirmed|min:6' : '',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        /** Actualizamos los datos del usuario */
        $user->name = $request->name;
        $user->email = $request->email;
        // Remove old model phone
        // $user->telefono = $request->telefono;

        foreach ($request->phones as $phone) {
            if ( (!$phone['number'] && !$phone['id'])
                || ($phone['deleted'] && !$phone['id'])) {
                continue;
            }

            $phoneUser = ($phone['id']) ? PhoneUser::find($phone['id']) : new PhoneUser();
            if($phone['deleted']) {
                $phoneUser->delete();
            } else {
                $phoneUser->phone_number = $phone['number'];
                $phoneUser->user_id = $user->id;
                $phoneUser->save();
            }
        }

        if($request->password){
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json('Residente modificado correctamente.', 201);
    }

    public function destroy($id)
    {
        $residente_conjunto = ResidenteConjunto::find($id);
        $residente_conjunto->delete();

        return response()->json('Residente eliminado correctamente.', 201);
    }
}
