<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Currency;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NbpController extends Controller
{
    public function get_currency_data_from_NBP_API (){
        $response = Http::acceptJson()->get('http://api.nbp.pl/api/exchangerates/tables/A/');
        if ($response->successful() ){ 
           $data = $response->json()[0]['rates'];

           $data = array_map(function($data) {
               return array(
                   'name' => $data['currency'],
                   'currency_code' => $data['code'], // we have to translate Response from NBP api so we coud insert thoes data in proper columns
                   'exchange_rate' => $data['mid']
               );
           }, $data);
        }
        else {  
            Log::channel('currencyNBP')->info(now()->toDateTimeString() . ' import from NBP failed ');
        }
        return $data;
    }


    public function update_currncy_table(){

        $data = $this->get_currency_data_from_NBP_API();
       
        foreach($data as $d){
                $currency = Currency::where('name',$d['name']);
               
            if ($currency->exists()){
                try {
                    $currency->update(['exchange_rate' => $d['exchange_rate'],"updated_at" => now() ]);
                    Log::channel('currencyNBP')->info(now()->toDateTimeString() . ' succesfull update of ' .$d['name']);
                }catch(\Exception $e){
                    Log::channel('currencyNBP')->info(now()->toDateTimeString() . ' import of '. $d['name'].' failed messeage: ' . $e->getMessage());
                } 
            }
            else{ 
                try{
                    
                    $currency = Currency::create($d);
                    Log::channel('currencyNBP')->info(now()->toDateTimeString() . ' succesfull insert of '. $d['name']);
                   
                } catch(\Exception $e){
                    Log::channel('currencyNBP')->info(now()->toDateTimeString() . ' import of '. $d['name'].' failed messeage: ' . $e->getMessage());
                } 
            }
     
        }
    }
    /**
     * @OA\Get(
     *      path="/user/subscribed_currencies/available",
     *      tags={"SubscribedCurrency"},
     *      security={ {"sanctum": {} }},
     *      summary="check available currencies to subscribe", 
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(), 
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * 
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
    public function available_currencies(Request $request){
            
        $validator = Validator::make(['limit' => $request->query('limit')], [
            'limit'=>'nullable|integer',        
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        $limit = $validator->validated()['limit'] ? (int)$validator->validated()['limit'] : 25;
       
        $currencies  = Currency::paginate($limit)->withQueryString();
       
        return response()->json($currencies,200);
        
    }
}
