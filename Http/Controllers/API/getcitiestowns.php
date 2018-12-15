<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\user;
use App\City;
use Exception;

class getcitiestowns extends Controller
{
    //This api will be used to get the cities and the towns in the application
    public function getCitiesTowns(Request $request){
        try{
            $apiToken= $request->apiToken;

            if(!$apiToken){
                return response()->json(['status'=>403]);
            }
            else{
                $user= User::where('apitoken','=',$apiToken)->first();
                if(!$user){
                    return response()->json(["status"=>401]);
                }
                else{
                    if($user->is_active == 0){
                        return response()->json(["status"=>401]);
                    }
                    else{
                        if(!$request->lang){
                            return response()->json(["status"=>400]);
                        }
                        else{
                            if($request->lang=="en"){
                                $response=array("status"=>200);
                                $cities = City::with(array("towns"))->select('id','name_en')->get();
                                
                                foreach ($cities as $city) {
                                    $city["city"]=$city["name_en"];
                                    unset($city["name_en"]);
                                    foreach($city["towns"] as $town){
                                        $town["town"]=$town["name_en"];
                                        unset($town["name_en"]);
                                        unset($town["name_ar"]);
                                        unset($town["delivery_price"]);
                                        unset($town["cities_id"]);
                                    }
                                }
                                $response["cities"]=$cities;
                                return response()->json($response);
                                
                            }
                            elseif($request->lang=="ar"){
                                $response=array("status"=>200);
                                $cities = City::with(array("towns"))->select('id','name_ar')->get();
                                
                                foreach ($cities as $city) {
                                    $city["city"]=$city["name_ar"];
                                    unset($city["name_ar"]);
                                    foreach($city["towns"] as $town){
                                        $town["town"]=$town["name_ar"];
                                        unset($town["name_ar"]);
                                        unset($town["name_en"]);
                                        unset($town["delivery_price"]);
                                        unset($town["cities_id"]);
                                    }
                                }
                                $response["cities"]=$cities;
                                return response()->json($response);

                            }
                            else{
                                return response()->json(["status"=>204]);
                            }
                        }
                    }
                }
            }

        }
        catch(Exception $e){
            return response()->json(['status'=>404]);
        }
    }
}
