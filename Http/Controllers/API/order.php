<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DB;
use Exception;
class order extends Controller
{
    /*This api will be used to add new order.
 
If isPromotion is true you have to check if the dessertSizeId is under promotion you have to calculate the new price of the dessert according to the discount percentage.
 
Notes: 
-The user shouldn’t be able to add new orders if he has a order with status “waiting”.
-The Delivery price should be calculating according to the town of the request (Look Up the price in the Towns Table) also assign the request to the nearest branch to it.
-If the order isDelivery order you should convert the latlong to address and store it.
        */






    public function addOrder(Request $request){
        try{
            $messages = [
                        'apiToken.required' => 'requiredapiToken',
                        'apiToken.exists' => 'apiTokenNotValid',
                        'isDelivery.required' => 'requiredisDelivery',
                        'isDelivery.in' => 'isDeliveryNotValid',
                        'delivery.latitude.required' => 'requiredDelivery[latitude]',
                        'delivery.latitude.numeric' => 'delivery[latitude]NotValid',
                        'delivery.longitude.required' => 'requiredDelivery[longitude]',
                        'delivery.longitude.numeric' => 'delivery[longitude]NotValid',
                        'delivery.placeType.required' => 'requiredDelivery[placeType]',
                        'delivery.placeType.in' => 'Delivery[placeType]NotValid',
                        'delivery.floorNumber.numeric' => 'delivery[floorNumber]NotValid',
                        'branchId.numeric' => 'branchIdNotValid',
                        'orders.isSpecial.required' => 'requiredOrder[isSpecial]',
                        'orders.isSpecial.in' => 'Order[isSpecial]NotValid',
                        'orders.cakeId.numeric' => 'Order[isSpecial]NotValid',
                        'orders.sauceId.numeric' => 'Order[sauceId]NotValid',
                        'orders.addonId.numeric' => 'Order[addonId]NotValid',
                        'orders.isPromotion.in' => 'Order[isPromotion]NotValid',
                        'orders.dessertSizeId.numeric' => 'Order[dessertSizeId]NotValid',
                        'orders.quantity.numeric' => 'Order[quantity]NotValid',
                        ];
           $rule=[
                        'apiToken'=>'required|min:64|exists:users,apitoken',
                        'isDelivery'=>['required',Rule::in([0, 1])],
                        'delivery.latitude'=>'required|numeric',
                        'delivery.longitude'=>'required|numeric',
                        'delivery.placeType'=>['required',Rule::in(['home','office','flat'])],
                        'delivery.floorNumber'=>'numeric',
                        'branchId'=>'numeric',
                        'orders.isSpecial'=>['required',Rule::in([0,1])],
                        'orders.cakeId'=>'numeric',
                        'orders.sauceId'=>'numeric',
                        'orders.addonId'=>'numeric',
                        'orders.isPromotion'=>[Rule::in([0,1])],
                        'orders.dessertSizeId'=>'numeric',
                        'orders.quantity'=>'numeric',

                        ];

           
            
            $Validator = Validator::make($request->all(),$rule,$messages);
            
            if($Validator->fails()){
                foreach ($Validator->errors()->all() as  $error) {
                    if($error=='apiTokenNotValid'){
                        return response()->json(['status'=>401]);
                    }
                    else{
                        return response()->json(['status'=>400]);
                    }
                }
            }
            else{
                $user = DB::table('users')->where('apitoken',$request->apiToken)->first();
                if($user->is_active == 1){
                    $oldOrders = DB::table('orders')->where('users_id',$user->id)->first();
                    if($oldOrders->status=="waiting"){
                        return response()->json(['status'=>411]);
                    }
                    else{
                        if($request->isSpecial==1){
                            $cakePrice = DB::table('cakes')->select("price")->where('id',$request->cakeId)->first();
                            $saucePrice = DB::table('sauces')->select("price")->where('id',$request->sauceId)->first();
                            $addonPrice = DB::table('addons')->select("price")->where('id',$request->addonId)->first();
                            $dessertPrice = DB::table('dessertsizes')->select("price")->where('id',$request->dessertSizeId)->first();
                            $branches = DB::table('branches')->select("latitude", "logitude")->get();
                            // measure the short distance and the price of it
                            foreach($branches as $branch){
                               $dist[] = DistanceBetweenPlaces( $branch->latitude , $branch->logitude , $request->delivery['latitude'] , $request->delivery['longitude'] );
                               $i = array_keys($dist, min($dist));
                               $theBestBranch = $branches[$i];
                            }
                            if($request->isPromotion==1){
                                $discountPer = DB::table('dessertpromotions')->select("discount_percentage")->where('dessertSizes_id',$request->dessertSizeId)->first();
                                $discount=( $discountPer * $dessertPrice ) / 100 ;
                                $dessertPrice = $dessertPrice - $discount ;
                                $total = ( ( $cakePrice + $saucePrice + $addonPrice ) * $dessertPrice ) * $request->quantity;
                            }
                            else{
                                $total = ( ( $cakePrice + $saucePrice + $addonPrice ) * $dessertPrice ) * $request->quantity;
                            }

                        }
                        elseif($request->isSpecial==0){
                            if(isset($request->addonId)){
                                $addonPrice = DB::table('addons')->select("price")->where('id',$request->addonId)->first();
                                $dessertPrice = DB::table('dessertsizes')->select("price")->where('id',$request->dessertSizeId)->first();
                                if($request->isPromotion==1){
                                $discountPer = DB::table('dessertpromotions')->select("discount_percentage")->where('dessertSizes_id',$request->dessertSizeId)->first();
                                $discount=( $discountPer * $dessertPrice ) / 100 ;
                                $dessertPrice = $dessertPrice - $discount ;
                                $total = ( $addonPrice  * $dessertPrice ) * $request->quantity;
                                }
                                else{
                                $total = (  $addonPrice  * $dessertPrice ) * $request->quantity;
                                }
                            }
                            else{
                                $dessertPrice = DB::table('dessertsizes')->select("price")->where('id',$request->dessertSizeId)->first();
                                if($request->isPromotion==1){
                                $discountPer = DB::table('dessertpromotions')->select("discount_percentage")->where('dessertSizes_id',$request->dessertSizeId)->first();
                                $discount=( $discountPer * $dessertPrice ) / 100 ;
                                $dessertPrice = $dessertPrice - $discount ;
                                $total =  $dessertPrice  * $request->quantity;
                                }
                                else{
                                    $total =  $dessertPrice  * $request->quantity;
                                }
                            }
                        }
                    }    
                }
                
                elseif($user->is_active == 0){
                    return response()->json(['status'=>401]);
                }
            }
        }
        catch(Exception $e){
            return $e;
        }
             
    }
    public static function DistanceBetweenPlaces($lat1 , $lon1 , $lat2 , $lon2){
        $Dlon=deg2rad($lon2 - $lon1);
        $Dlat=deg2rad($lat2 - $lot1);
        $a = ( sin($Dlat / 2) * sin($Dlat / 2) ) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * (sin($Dlon / 2) * sin($Dlon / 2));
        $angel = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return ($angel * 6378.16 );
    }
}
