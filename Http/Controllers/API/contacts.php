<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\contact;
use DB;
use Exception;

class contacts extends Controller
{
    //This api will be used to send messages to the application support.
    public function contact(Request $request ){
        try{
            $messages = [
                        'apiToken.required' => 'requiredapiToken',
                        'apiToken.exists' => 'apiTokenNotValid',
                        'message.required' => 'messageRequired',
                        'type.required' => 'typeRequired',
                        'type.in' => 'typeRequired',
                        ];
            $rule=[
                        'apiToken'=>'required|min:64|exists:users,apitoken',
                        'type' => ['required',Rule::in(['user', 'driver'])],
                        'message' =>'required',

                ];
            $Validator = Validator::make($request->all(),$rule,$messages);

            if($Validator->fails()){
                foreach ($Validator->errors()->all() as  $error) {
                    if($error =='requiredapiToken' ||$error=='messageRequired'||$error=='typeRequired'){
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
                    $contact = new contact;
                    $contact->message=$request->message;
                    $contact->users_id=$user->id;
                    $contact->save();
                    return response()->json(['status'=>204]);
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


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //This api will be used to get the contact us info.
    public function contactInfo(Request $request ){
        try{
            $messages = [
                        'apiToken.required' => 'requiredapiToken',
                        'apiToken.exists' => 'apiTokenNotValid',
                        'type.required' => 'typeRequired',
                        ];
            $rule=[
                        'apiToken'=>'required|min:64|exists:users,apitoken',
                        'type' => ['required',Rule::in(['user', 'driver'])],

                ];
            $Validator = Validator::make($request->all(),$rule,$messages);

            if($Validator->fails()){
                foreach ($Validator->errors()->all() as  $error) {
                    if($error =='requiredapiToken' || $error=='typeRequired'){
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
                    return response()->json(['status'=>200,'email'=>$user->email,'phones'=>$user->phone]);
                }
                elseif($user->is_active == 0){
                    return response()->json(['status'=>401]);
                }
            }
        }
        catch(Exception $e){
            return $e; //response()->json(['status'=>404]);
        }
    }
}
