<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use DB;
use Exception;

class aboutus extends Controller
{
    //This api will be used to get the about us app message in the language the app currently uses
    public function aboutus(Request $request){
        try{
            $messages = [
                        'apiToken.required' => 'requiredapiToken',
                        'apiToken.exists' => 'apiTokenNotValid',
                        'lang.required' => 'langRequired',
                        'type.required' => 'typeRequired',
                        'lang.in' => 'langRequired',
                        'type.in' => 'typeRequired',
                        ];
            $rule=[
                        'apiToken'=>'required|min:64|exists:users,apitoken',
                        'type' => ['required',Rule::in(['user', 'admin'])],
                        'lang' => ['required',Rule::in(['ar', 'en'])],

                ];
            $Validator = Validator::make($request->all(),$rule,$messages);

            if($Validator->fails()){
                foreach ($Validator->errors()->all() as  $error) {
                    if($error =='requiredapiToken' ||$error=='langRequired'||$error=='typeRequired'){
                        return response()->json(['status'=>400]);
                    }
                    elseif($error=='apiTokenNotValid'){
                        return response()->json(['status'=>401]);
                    }
                }
            }
            else{
                $user = DB::table('users')->where('apitoken',$request->apiToken)->first();
                if($user->is_active == 1){
                    if($request->lang=="ar"){
                        $data = DB::table('appsettings')->select('about_us_ar AS message')->get();
                        $message = $data[0];
                        return response()->json(['status'=>200,'message'=>$message->message]);
                    }
                    elseif($request->lang=="en"){
                        $data = DB::table('appsettings')->select('about_us_en AS message')->get();
                        $message = $data[0];
                        return response()->json(['status'=>200,'message'=>$message->message]);
                    }
                }
                elseif($user->is_active == 0){
                    return response()->json(['status'=>401]);
                }
            }
        }
        catch(Exception $e){
            return response()->json(['status'=>404]);
        }

    }
}
