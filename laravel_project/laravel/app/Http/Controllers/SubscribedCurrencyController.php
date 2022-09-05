<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscribedCurrency;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Currency;

class SubscribedCurrencyController extends Controller
{
 /**
     * @OA\Get(
     *      path="/user/subscribed_currencies",
     *      tags={"SubscribedCurrency"},
     *      security={ {"sanctum": {} }},
     *      summary="check account balance", 
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(), 
     *       ),
     * 
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *      ),
     *        @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *      ),
     *     
     *     )
     */
    public function index(Request $request){
       
        
        $validator = Validator::make(['limit' => $request->query('limit')], [
            'limit'=>'nullable|integer',        
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        $limit = $validator->validated()['limit'] ? (int)$validator->validated()['limit'] : 25;
       
        $user = auth()->user();
        try{

            $subscribed_currency =  SubscribedCurrency::select('subscribed_currencies.id','currencies.name','currencies.currency_code',
            'currencies.exchange_rate','currencies.updated_at',)
            ->join('currencies', 'subscribed_currencies.subscribed_currency_name', '=', 'currencies.name')
            ->where('user_id',$user->id)
            //dd($subscribed_currency->toSql());
            ->paginate($limit)->withQueryString();

            return response()->json($subscribed_currency,200);
        }catch(\Exception $exception){       
            Log::channel('subscribedCurrency')->info(now()->toDateTimeString() . ' something unexpected happend in: SubscribedCurrencyController while getting data from database in index function ' . $exception->getMessage() . ' ' . $user );
            return response()->json($exception->getMessage(),500);
        }

        
    }

    /**
     * @OA\Post(
     *      path="/user/subscribed_currencies",
     *      tags={"SubscribedCurrency"},
     *      security={ {"sanctum": {} }},
     *      summary="Add Subscribed Currency",
     *     
     *      @OA\RequestBody(
     *           
     *           required=true,
     *           description="pass data to create Subscribed Currency record",
        *      @OA\JsonContent(
        *          @OA\Property(property = "subscribed_currency_name", type="string", example="euro",description = "currency name")
        *      )
     *      ),
     * 
     *      @OA\Response(
     *          response=201,
     *          description="Created",
     *          @OA\JsonContent(),
     *          
     *       ),
     *  *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * 
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable entity",
     *      ),
     * 
     * 
     *       @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *      ),
     *     
     *     )
     */
    public function store(Request $request){

        $currency = $request->validate([      
            'subscribed_currency_name'=>'required|string',        
        ]);
        $currency = $currency['subscribed_currency_name'];
        $user = auth()->user();
       
        try{

            if(SubscribedCurrency::where('user_id',$user->id)->where('subscribed_currency_name',$currency)->exists()){

                return response()->json(['message' => 'This user have subscribed this currency'],422);
            }

            if(Currency::where('name',$currency)->doesntExist()){

                return response()->json(['message' => 'Currency not found'],404);
            }

            $subscribed_currency = SubscribedCurrency::create([
                'user_id' => $user->id,
                'subscribed_currency_name' => $currency       
            ]);

            return response()->json(['message' => 'Currency succefully subscribed'],201);
        }
        catch(\Exception $exception){       
            Log::channel('subscribedCurrency')->info(now()->toDateTimeString() . ' something unexpected happend in: SubscribedCurrencyController ' . $exception->getMessage());
            return response()->json($exception->getMessage(),500);
        }
    }


     /**
     * @OA\Delete(
     *      path="/user/subscribed_currencies/{currency_name}",
     *      tags={"SubscribedCurrency"},
     *      security={ {"sanctum": {} }},
     *      summary="deletes one subscribed currency record of particular user",
     *      @OA\Parameter(
    *           name="currency_name",
    *           required=true,
    *           in="path",
    *           description="currency_name for example: euro",
     *      ),
     * 
     *      @OA\Response(
     *          response=204,
     *          description="No Content",
     *          @OA\JsonContent(), 
     *       ),
     * 
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *      ),
     *   @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *      ),
     *     
     *     )
     */
    public function delete(Request $request, $currency_name){
        
        $validator = Validator::make(['currency_name' =>$currency_name], [
            'currency_name' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
         }
         $currency = $validator->validated()['currency_name'];
        

        if(SubscribedCurrency::where('subscribed_currency_name',$currency)->doesntExist()){

            return response()->json(['message' => 'Subscribed currency not found'],404);
        }
       
        $user = auth()->user();

        $deletion = SubscribedCurrency::where('user_id',$user->id)
        ->where('subscribed_currency_name',$currency)
        ->delete();

        if ($deletion == 1){
            return response()->json(['message' => 'Subscribed currency succesfully deleted'],204);
    
        }else{
            Log::channel('subscribedCurrency')->info(now()->toDateTimeString() . ' Failed deletion in subscribed_currency table in psotgres database ' . $user);
            return response()->json(['message' => 'Something went wrong while deleting subscribed currency record'],500);
        }    
    }
/**
     * @OA\Delete(
     *      path="/user/subscribed_currencies",
     *      tags={"SubscribedCurrency"},
     *      security={ {"sanctum": {} }},
     *      summary="deletes all subscribed currency records of particular user",
     *   
     *      @OA\Response(
     *          response=204,
     *          description="No Content",
     *          @OA\JsonContent(),
     *       ),
     * 
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *      ),
     *   @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *      ),
     *     
     *     )
     */
    public function delete_all(Request $request){
 
        $user = auth()->user();

        $deletions = SubscribedCurrency::where('user_id',$user->id)
        ->delete();

        if ($deletions){
            return response()->json(['message' => 'All records of subscribed currency succesfully deleted', 'amount' => $deletions ],204);
    
        }else{
            Log::channel('subscribedCurrency')->info(now()->toDateTimeString() . ' Failed massive deletion in subscribed_currency table in psotgres database ' . $user);
            return response()->json(['message' => 'Something went wrong while deleting subscribed currency records'],500);
        }    
    }
}
