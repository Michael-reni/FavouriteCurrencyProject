<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Currency;
use Illuminate\Support\Facades\Log;

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
}
