<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Session;
use App\user;
use Exception;
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;
use Ivory\HttpAdapter\FopenHttpAdapter;



class register extends Controller
{
    //This api will be used to register new user
    public function register(Request $request){
        $config = new Configuration();
$config->setFirebaseSecret('vzhClwpGBuD5whUI41eeBhc1KHRBSoNZ6NJPUfXc');
$http = new FopenHttpAdapter(); // or any other available adapter

$config->setHttpAdapter($http);
$firebase = new Firebase('https://dokhn-80554.firebaseio.com', $config);

        try{


                $messages = [
                        'fname.required'=>'firstNameRequired',
                        'lname.required'=>'lastNameRequired',
                        'phone.required'=>'phoneRequired',
                        'phone.min'=>'phoneNotValid',
                        'phone.numeric'=>'phoneNotValid',
                        'phone.unique'=>'phoneNotValid',
                        'email.required'=>'emailRequired',
                        'email.min'=>'emailNotValid',
                        'email.unique'=>'emailNotValid',
                        'cityId.required'=>'cityIdRequired',
                        'cityId.min'=>'cityIdNotValid',
                        'password.required'=>'passwordRequired',
                        'password.min'=>'passwordNotValid',
                ];

                $rule=[
                        'firstName'=>'required',
                        'lastName'=>'required',
                        'phone' => 'required|numeric|min:9|unique:users,phone',
                        'email'=>'required|email|min:5|unique:users,email',
                        'cityId'=>'required',
                        'password'=>'required|min:6',
                ];

                $Validator = Validator::make($request->all(),$rule,$messages);

                if($Validator->fails()){
                    foreach ($Validator->errors()->all() as  $error) {
                        if(
                            $error =='firstNameRequired' || $error=='lastNameRequired' || $error =='phoneRequired'||
                            $error =='emailRequired' || $error =='passwordRequired' || $error =='cityIdRequired')
                        {
                            return response()->json(['status'=>400]);
                        }
                        elseif ($error == 'passwordNotValid') {
                            return response()->json(['status'=>400]);
   	                    }
                        elseif ($error == 'phoneNotValid') {
                            return response()->json(['status'=>400]);
   	                    }
                        elseif ($error == 'emailExists') {
                            return response()->json(['status'=>405]);
   	                    }
                        elseif ($error == 'phoneIsExists') {
   	                        return response()->json(['status'=>406]);	
   	                    }
                    }
                }
                else{
                    $user=new User;
                    $user->first_name=$request->firstName;
                    $user->last_name=$request->lastName;
                    $user->phone=$request->phone;
                    $user->email=$request->email;
                    $user->cities_id=$request->cityId;
                    $user->towns_id=$request->towns_id;
                    $user->town=$request->town;                    
                    $user->password=Hash::make($request->password);
                    $user->apitoken=str_random(64);
   
                    if($user->save()){
	  		            $dateTime=Carbon::now();
			  		    //$firebase->set($request->phone,['code'=>123456,'time'=>$dateTime->addHour()->toTimeString()]);
                          $firebase->set('object', 'value_1');
   	                    return response()->json(['status'=>204]);
                    }
                }
        }
            
        
        catch(Exception $e){

            return $e; //response()->json(['status'=>404]);
        }
    }

}