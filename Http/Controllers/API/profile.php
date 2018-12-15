<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use DB;
use App\user;
use Carbon\Carbon;
use Session;
use Mail;
use Exception;


class profile extends Controller
{
    //This api will be used to reset the password of the user if his account is active by sending a verification code
    public function forgetPassword(Request $request){
        try{
            $messages=[
                'phone.required'=>'phoneRequired',
                'phone.min'=>'phoneNotValid',
                'phone.numeric'=>'phoneNotValid',
                'phone.exists'=>'phoneNotExists',
            ];
            $rule=[
                'phone' => 'required|numeric|min:9|exists:users,phone'
            ];
            $Validator = Validator::make($request->all(),$rule,$messages);

            if($Validator->fails()){
                foreach ($Validator->errors()->all() as  $error) {
                    if( $error =='phoneNotValid'|| $error =='phoneRequired')
                    {
                        return response()->json(['status'=>400]);
                    }
                    elseif($error =='phoneNotExists'){
                        return response()->json(['status'=>408]);
                    }
                }
            }
            else{
                $user = DB::table('users')->where('phone',$request->phone)->first();
                if($user->is_active==1){
                    $code="123456";
                    Mail::raw($code,function ($message)use ($user){
                        $message->to($user->email)->subject('This email to reset password');
                        $message->from('Dokhn@services.com');
                    });
                    $dateTime=Carbon::now();
                    session()->put($request->phone,['code'=>"123456",'time'=>$dateTime->addHour()->toTimeString()]);
                    return response()->json(['status'=>202]);

                }
                elseif($user->is_active==0){
                    return response()->json(['status'=>401]);
                }
            }




        }
        catch(Exception $e){
            return response()->json(['status'=>404]);
        }
    }

    public function changePassword(Request $request){
        try{
            $messages=[
                'tmpToken.min'=>'tmpTokenNotValid',
                'tmpToken.exists'=>'tmpTokenNotValid',
                'newPassword.required'=>'newPasswordRequired',
                'newPassword.min'=>'newPasswordNotValid',
            ];
            $rule=[
                'tmpToken'=>'min:64|exists:users,apitoken',
                'newPassword'=>'required|min:6',
                
            ];
            $Validator = Validator::make($request->all(),$rule,$messages);

            if($Validator->fails()){
                foreach ($Validator->errors()->all() as  $error) {
                    if( $error =='newPasswordNotValid'|| $error =='newPasswordRequired')
                    {
                        return response()->json(['status'=>400]);
                    }
                    elseif($error =='tmpTokenNotExists'|| $error =='tmpTokenNotValid' ){
                        return response()->json(['status'=>401]);
                    }
                }
            }
            else{
                $user = DB::table('users')->where('apitoken',$request->tmpToken)->first();
                if($user->is_active == 1){
                     $hashpassword=Hash::make($request->newPassword);
                     DB::table('users')->where('apitoken',$request->tmpToken)->update(['password' =>  $hashpassword]);
                     return response()->json(['status'=>204]);
                }
                elseif($user->is_active == 0){

                    return response()->json(['status'=>401]);
                }
                else{
                    return response()->json(['status'=>401]);
                }
            }

        }
        catch(Exception $e){
            return response()->json(['status'=>404]);
        }
    }




    function updateProfile(Request $request){
        try{
            $messages = [
                        
                    'email.email'=>'emailNotValid',
                    'email.min'=>'emailNotValid',
                    'email.unique'=>'emailNotValidUnique',
                    'apiToken.required'=>'tokenNotValid',
                    'apiToken.min'=>'tokenNotValid',
                    'apiToken.exists'=>'tokenNotValid',

                ];

                $rule=[
                        'apiToken'=>'required|min:64|exists:users,apitoken',
                        'email'=>'email|min:5|unique:users,email',
                ];

                $Validator = Validator::make($request->all(),$rule,$messages);

                if($Validator->fails()){
                    foreach ($Validator->errors()->all() as  $error) {
                        if($error =='emailNotValid' || $error =='tokenNotValid')
                        {
                            return response()->json(['status'=>400]);
                        }
                       
                        elseif ($error == 'emailNotValidUnique') {
   	                        return response()->json(['status'=>405]);	
   	                    }
                    }
                }
                else{
                    $user = DB::table('users')->where('apitoken',$request->apiToken)->first();
                    if($user->is_active==1){
                        $values['first_name']=$request->firstName;
                        $values['last_name']=$request->lastName;
                        $values['email']=$request->email;
                        $values['cities_id']=$request->cityId;
                        $values['towns_id']=$request->townId;
                        $values['town']=$request->town;
                        DB::table('users')->where('apitoken',$request->apiToken)->update(array_filter($values));
                        return response()->json(['status'=>204]);
                    }
                    elseif($user->is_active==0){
                        return response()->json(['status'=>401]);
                    }

                }
        }
        catch(Exception $e){
            return response()->json(['status'=>404]);
        }
    }

    public function updatePassword(Request $request){
        try{


                $messages = [
                        'apiToken.required'=>'apiTokenRequired',
                        'apiToken.min'=>'apiTokenNotValid',
                        'apiToken.exists'=>'apiTokenNotValid',
                        'oldPassword.required'=>'passwordRequired',
                        'oldPassword.min'=>'passwordNotValid',
                        'newPassword.required'=>'passwordRequired',
                        'newPassword.min'=>'passwordNotValid',
                ];

                $rule=[
                        'apiToken'=>'required|min:64|exists:users,apitoken',
                        'oldPassword'=>'required|min:6',
                        'newPassword'=>'required|min:6',
                ];

                $Validator = Validator::make($request->all(),$rule,$messages);

                if($Validator->fails()){
                    foreach ($Validator->errors()->all() as  $error) {
                        if($error =='apiTokenRequired' || $error=='apiTokenNotValid' || $error =='passwordRequired'|| $error =='passwordNotValid')
                        {
                            return response()->json(['status'=>400]);
                        }
                    }
                }
                else{
                    $user = DB::table('users')->where('apitoken',$request->apiToken)->first();
                    if(Hash::check($request->oldPassword,$user->password)){
                        if($user->is_active==1){
                            $hashedpassword=Hash::make($request->newPassword);
                            DB::table('users')->where('apitoken',$request->apiToken)->update(['password'=>$hashedpassword]);
                            return response()->json(['status'=>204]);
                        }
                        elseif($user->is_active==0){
                            return response()->json(['status'=>400]);
                        }
                    }
                    else{
                        return response()->json(['status'=>410]);
                    }
                    
                }
        }
        catch(Exception $e){
            return response()->json(['status'=>404]);
        }
    }




    public function updatePhone(Request $request){
        try{
            $messages = [
                        'apiToken.required'=>'apiTokenRequired',
                        'apiToken.min'=>'apiTokenNotValid',
                        'apiToken.exists'=>'apiTokenNotValid',
                        'phone.required'=>'phoneRequired',
                        'phone.min'=>'phoneNotValid',
                        'phone.numeric'=>'phoneNotValid',
                        'phone.unique'=>'phoneNotUnique',
                        ];
            $rule=[
                        'apiToken'=>'required|min:64|exists:users,apitoken',
                        'phone' => 'required|numeric|min:9|unique:users,phone'

                ];
            $Validator = Validator::make($request->all(),$rule,$messages);

            if($Validator->fails()){
                foreach ($Validator->errors()->all() as  $error) {
                    if($error =='apiTokenRequired' || $error=='apiTokenNotValid'||$error=='phoneRequired'||$error=='phoneNotValid'){
                        return response()->json(['status'=>400]);
                    }
                    elseif($error =='phoneNotUnique'){
                        return response()->json(['status'=>406]);
                    }
                }
            }
            else{
                $user = DB::table('users')->where('apitoken',$request->apiToken)->first();
                    if($user->is_active==1){
                        session()->put($request->apiToken,['code'=>456789,'newPhone'=>$request->phone]);
   	                    return response()->json(['status'=>204]);
                    }
                    elseif($user->is_active==0){
                         return response()->json(['status'=>401]);
                    }
            }
        }
        catch(Exception $e){
            return $e; // response()->json(['status'=>404]);
        }

    }


}
