<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($flag)
    {
        //flag can be 1/0 Active user or deactive user
        $query= User::select('email','name');
        if ($flag ==1)
        {
            $query->where('status',1);
        }
        elseif ($flag==0)
        {
            //all
        }
        else{
            return response()->json([
                'message'=>"invaile parametre passed paramiter willbe 0/1",
                'status'=>0,
            ],400);
        }
        $users=$query->get();
        if(count($users) >0 )
        {
            $response=[
                    'message'=> count($users). 'user found',
                'status'=> 1,
                'data'=>$users
            ];
        }
        else{
            $response=[
                'message'=> count($users). 'user found',
                'status'=> 1,
                'data'=>$users
            ];
        }
        return response()->json($response,200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator =Validator::make($request->all(),[
            'name'=>['required'],
            'email'=>['required','email','unique:users,email'],
            'password'=>['required','min:8','confirmed'],
            'password_confirmation'=>['required'],
        ]);
        if($validator->fails())
        {
            return response()->json($validator->messages(),400);
        }
        else {
            $data=[
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
            ];
            DB::beginTransaction();
            try {
                $user=User::create($data);
                DB::commit();

            }
            catch (\Exception $exception){
                DB::rollBack();
                $user = null;
//                p($exception->getMessage());
            }
            if($user!= null){
                return response()->json([
                    'message'=>'data save successfully',
                ],200);
            }
            else{
                return response()->json([
                    'message'=>'Internal Server error',
                ],500);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       $user=User::find($id);
       if(is_null($user))
       {
           $response= [
               'message'=>'User not Fund',
               'status'=>0
           ];
       }
       else{
           $response=[
               'message'=>'User found',
               'status' => 1,
               'data'   =>$user
           ];
       }
       return response()->json($response,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user=User::find($id);
        if(is_null($user))
        {
            return response()->json(
                [
                    'status'=>0,
                    'message'=>"user Not Found",
                ],
                404
            );
        }
        else{
            DB::beginTransaction();
            try {
                $user->name=$request['name'];
                $user->email=$request['email'];
                $user->contact=$request['contact'];
                $user->pinCode=$request['pinCode'];
                $user->address=$request['address'];
                $user->save();
                DB::commit();

            }
            catch (\Exception $exception)
            {
                DB::rollBack();
                $user=null;
                if(is_null($user)){
                    return response()->json(
                        [
                            'status'=>0,
                            'message'=>"internal server error",
                            'errMessage'=>$exception->getMessage(),
                        ],
                        500
                    );
                }
                else{
                    return response()->json(
                        [
                            'status'=>1,
                            'message'=>"Data update Successfully",
                        ], 300
                    );
                }

            }


        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user=User::find($id);
        if(is_null($user))
        {
            $response=[
                'message'=>'User not Found',
                'status'=>0,
            ];
            $respCode =404;
        }
        else{
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response=[
                    'message'=>'user deleted successfully',
                    'status'=>1
                ];
                $respCode=200;

            }
            catch (\Exception $exception)
            {
                DB::rollBack();
                $response=[
                    'message'=>'Internal Server error',
                    'status'=>0
                ];
                $respCode=500;


            }
        }
        return response()->json($response,$respCode);
    }
}
