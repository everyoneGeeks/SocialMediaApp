<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use App\user;
use Exception;

class validate extends Controller
{
    //This api will be used in 3 cases to validate the user verification code
    public function validateCode(Request $request){

        try{


                $messages = [
                        'phone.min'=>'phoneNotValid',
                        'phone.numeric'=>'phoneNotValid',
                        'phone.exists'=>'phoneNotValid',
                        'apiToken.min'=>'apiNotValid',
                        'apiToken.exists'=>'apiNotValid',
                        'code.required'=>'codeRequired',
                        'code.min'=>'codeNotValid',
                ];

                $rule=[
                        'phone' => 'numeric|min:9|exists:users,phone',
                        'apiToken'=>'min:64|exists:users,apitoken',
                        'code'=>'required|min:6',
                ];

                $Validator = Validator::make($request->all(),$rule,$messages);

                if($Validator->fails()){
                    foreach ($Validator->errors()->all() as  $error) {
                        if( $error =='phoneNotValid'|| $error =='apiNotValid' || $error =='codeRequired' || $error =='codeNotValid')
                        {
                            return response()->json(['status'=>400]);
                        }
                    }
                }
                else{
                    if(isset($request->phone)){
                        $user = DB::table('users')->where('phone',$request->phone)->first();
                        if($user->is_verified==0){
                            //Register Case
                            $value=session()->get($request->phone);
                            print_r($value);
                            if($session=session()->get($request->phone)){
                                if($session['code']==$request->code){
                                    DB::table('users')->where('phone',$request->phone)->update(['is_verified'=>1]);
                                    return response()->json(['status'=>200,'apiToken'=>$user->apitoken]);
                                }
                                else{
                                    return response()->json(['status'=>407]);
                                }
                            }
                            else{
                                 return response()->json(['status'=>4000]);
                            } 
                        }
                    elseif($user->is_verified==1){
                        if($session=session($request->phone)){
                            if($session['code']==$request->code){
                                $dateTime=Carbon::now();
                                $tmptoken=str_random(64);
                                session()->put($request->phone,['apiToken'=>$tmptoken,'time'=>$dateTime->addHour()->toTimeString()]);
                                return response()->json(['status'=>200,'tmpToken'=>$tmptoken]);
                            }
                            else{
                                return response()->json(["status"=>407]);
                            }
                        }
                        else{
                            return response()->json(['status'=>400]);
                        }
                    }
                }
                    elseif(isset($request->apiToken)){
                        $user = DB::table('users')->where('apiToken',$request->apiToken)->first();
                            if($session=session($request->apitoken)){
                                if($session['code']==$request->code){
                                    DB::table('users')->where('apitoken',$request->apitoken)->update(['phone'=>$seesion['newPhone']]);
                                    return response()->json(["status"=>204]);
                                }
                                else{
                                    return response()->json(["status"=>407]);
                                }
                            }
                            else{
                                return response()->json(['status'=>400]);
                            }
                        }
                    else{
                        return response()->json(['status'=>400]);
                    }
                }
            

        }
            
        
        catch(Exception $e){
            return $e;
            //Status : 404 if the request failed because of resource is not found or another unknown problem.
            //return response()->json(['status'=>404]);
        }






        
    }
}
