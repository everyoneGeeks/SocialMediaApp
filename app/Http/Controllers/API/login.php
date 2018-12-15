<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Session;
use DB;
use App\user;
use Exception;

class login extends Controller
{
    //This api will be used to login the existing users
    public function login(Request $request){
            try{
                $messages=[
                    'email.required'=>'emailRequired',
                    'email.min'=>'emailNotValid',
                    'email.email'=>'emailNotValid',
                    'email.exists'=>'emailNotExists',
                    'password.required'=>'passwordRequired',
                    'password.min'=>'passwordNotValid',
                ];
                $rule=[
                    'email'=>'required|email|min:5|exists:users,email',
                    'password'=>'required|min:6',
                    
                ];
                $Validator = Validator::make($request->all(),$rule,$messages);

                if($Validator->fails()){
                    foreach ($Validator->errors()->all() as  $error) {
                        if( $error =='passwordRequired' || $error =='passwordNotValid'|| $error =='emailRequired'||$error =='emailNotExists'|| $error =='emailNotValid')
                        {
                            return response()->json(['status'=>400]);
                        }
                    }
                }
                else{
                    $user=User::where('email',$request->email)->select('password','is_active','apitoken AS apiToken','first_name AS firstName','last_name AS lastName','email','cities_id AS cityId','towns_id AS townId','town')->first();
                    if(Hash::check($request->password,$user->password)){
	                    if($user->is_active == 1){
                            unset($user['is_active']);
                            return response()->json(['status'=>200,'user'=>$user]);
                        }
                        elseif($user->is_active == 0){

                        return response()->json(['status'=>409]);
                        }
                    }
                    else{
                        return response()->json(['status'=>400]);
                    }
                    
                }
            }

        catch(Exception $e){
            return response()->json(['status'=>404]);
        }

    }
}
