<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     
    public function Login (Request $request) {
        $fields = Validator::make($request->all(), [
            'email'=> 'required|string',
            'password'=> 'required|string|min:8',
        ]);
        
        if($fields->fails()) {
            $response = [
                'errors'=> $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'incorrect credentials',
                'success' => false
            ]);
        }
        // else if(is_null($user->email_verified_at)) {
        //     return response([
        //         'message' => 'email not verified',
        //         'success' => false
        //     ], 401);
        // }
        $token = $user->createToken('Personal Access Token', [])->plainTextToken;
        
            
        $response = [
            'user'=> $user,
            'token'=> $token,
            'message'=> 'logged in',
            'success' => true
        ];

        return response($response, 201);


    }

    public function Register (Request $request) {
        $fields = Validator::make($request->all(),[
            'name'=> 'required|string',
            'email'=> 'required|string|unique:users,email',
            'password'=> 'required|string|min:8',
            'agent'=> 'required|boolean',
        ]);


        if($fields->fails()) {
            $response = [
                'errors'=> $fields->errors(),
                'success' => false
            ];

            return response($response);
        }


        $user = User::create([
            'name'=> $request['name'],
            'email'=> $request['email'],
            'agent'=> $request['agent'],
            'password' => bcrypt($request['password']),
        ]);
        $response = [
            'user'=> $user,
            'message'=> 'successful signup',
            'success' => true
        ];

        return response($response);


    }

    public function Logout (Request $request) {
        $request->user()->tokens()->delete();

        return [
            'message'=> 'logged out'
        ];
    }



    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
