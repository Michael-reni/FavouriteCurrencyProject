<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class FavouriteCurrencyProjectTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_nbp_api_is_avaible_and_returns_data()
    {
        Artisan::call("NBP:currency");
     
        $this->assertDatabaseHas('currencies', [
            'name' => 'euro',
            'currency_code' => 'EUR'
        ]);  
    }

    public function test_all_secured_endpoints_returns_401_if_none_jwt_token_provided()
    {
        //api/user/subscribed_currencies/available
        
        $response = $this->get('/api/user'
        ,['Accept'=>'application/json']);
        
        $response->assertStatus(401);

        $response = $this->get('/api/user/subscribed_currencies/available'
        ,['Accept'=>'application/json']);
        
        $response->assertStatus(401);
       
        $response = $this->get('/api/user/subscribed_currencies'
        ,['Accept'=>'application/json']);
        
        $response->assertStatus(401);
        
        $data =  [ "subscribed_currency_name" => "euro"];
        $response = $this->post('/api/user/subscribed_currencies'
        ,$data
        ,['Accept'=>'application/json']);
        
        $response->assertStatus(401);

        $response = $this->json('delete', '/api/user/subscribed_currencies');

       
        $response->assertStatus(401);

        $response = $this->json('delete', '/api/user/subscribed_currencies/euro');

        
        $response->assertStatus(401);     
    }

    public function test_cannot_register_2_users_with_the_same_email()
    {
        $data =  [ "name"=> "Jan",
            "email" => "jankowalski@wp.pl",
            "password" => "strongpassword123"
        ];

        $response = $this->post('/api/user/register'
        ,$data
        ,['Accept'=>'application/json']); 

        $response->assertStatus(201);

        $response = $this->post('/api/user/register'
        ,$data
        ,['Accept'=>'application/json']); 

        $response->assertStatus(422);
        
        
    }

    public function test_viewing_user_works()
    {
        $data =  [ "name"=> "Jan",
            "email" => "jankowalski@wp.pl",
            "password" => "strongpassword123"
        ];

        $response = $this->post('/api/user/register'
        ,$data
        ,['Accept'=>'application/json']); 

        $response->assertStatus(201);
        $JWT_token = $response->decodeResponseJson()['access_token'];

        $response = $this->get('/api/user'
        ,['Accept'=>'application/json','Authorization' =>  $JWT_token]);
      
        $response->assertStatus(200)->assertJson([
           
            "name"=> "Jan",
            "email"=> "jankowalski@wp.pl"
           
        ]);
        
    }

    public function test_subscribing_currency_works()
    {
        $data =  [ "name"=> "Jan",
            "email" => "jankowalski@wp.pl",
            "password" => "strongpassword123"
        ];

        $response = $this->post('/api/user/register'
        ,$data
        ,['Accept'=>'application/json']); 

        $response->assertStatus(201);
        $JWT_token = $response->decodeResponseJson()['access_token'];

        $response = $this->get('/api/user'
        ,['Accept'=>'application/json','Authorization' =>  $JWT_token]);
        
        $user_id = $response->decodeResponseJson()['id'];
       
        Artisan::call("NBP:currency");
        $data =  [ "subscribed_currency_name" => "euro"];
        $response = $this->post('/api/user/subscribed_currencies'
        ,$data
        ,['Accept'=>'application/json','Authorization' =>  $JWT_token]); 

        $response->assertStatus(201);

        
        $this->assertDatabaseHas('subscribed_currencies', [
            'user_id' => $user_id,
            'subscribed_currency_name' =>   $data[ "subscribed_currency_name"]   
        ]);
    }


    public function test_unsubscribing_currency_works()
    {
        $data =  [ "name"=> "Jan",
            "email" => "jankowalski@wp.pl",
            "password" => "strongpassword123"
        ];

        $response = $this->post('/api/user/register'
        ,$data
        ,['Accept'=>'application/json']); 

        $response->assertStatus(201);
        $JWT_token = $response->decodeResponseJson()['access_token'];

        $response = $this->get('/api/user'
        ,['Accept'=>'application/json','Authorization' =>  $JWT_token]);

        $user_id = $response->decodeResponseJson()['id'];
       
        Artisan::call("NBP:currency");
        $data =  [ "subscribed_currency_name" => "euro"];
        $response = $this->post('/api/user/subscribed_currencies'
        ,$data
        ,['Accept'=>'application/json','Authorization' =>  $JWT_token]); 

        $response->assertStatus(201);

        
        $this->assertDatabaseHas('subscribed_currencies', [
            'user_id' => $user_id,
            'subscribed_currency_name' =>   $data[ "subscribed_currency_name"]   
        ]);

        $response = $this->withHeaders( ['Accept'=>'application/json','Authorization' =>  $JWT_token])->delete('/api/user/subscribed_currencies/' .$data[ "subscribed_currency_name"] );
      
        $response->assertStatus(204);

        $this->assertDatabaseMissing('subscribed_currencies', [
            'user_id' => $user_id,
            'subscribed_currency_name' =>   $data[ "subscribed_currency_name"]   
        ]);
    }

    public function test_unsubscribing_all_currency_works()
    {
        $data =  [ "name"=> "Jan",
            "email" => "jankowalski@wp.pl",
            "password" => "strongpassword123"
        ];

        $response = $this->post('/api/user/register'
        ,$data
        ,['Accept'=>'application/json']); 

        $response->assertStatus(201);
        $JWT_token = $response->decodeResponseJson()['access_token'];

        $response = $this->get('/api/user'
        ,['Accept'=>'application/json','Authorization' =>  $JWT_token]);

        $user_id = $response->decodeResponseJson()['id'];
       
        Artisan::call("NBP:currency");
        $data1 =  [ "subscribed_currency_name" => "euro"];
        $response = $this->post('/api/user/subscribed_currencies'
        ,$data1
        ,['Accept'=>'application/json','Authorization' =>  $JWT_token]); 

        $response->assertStatus(201);

         
      
        $data2 =  [ "subscribed_currency_name" => "rupia indyjska"];
        $response = $this->post('/api/user/subscribed_currencies'
        ,$data2
        ,['Accept'=>'application/json','Authorization' =>  $JWT_token]); 

        $response->assertStatus(201);

        
        $this->assertDatabaseHas('subscribed_currencies', [
            'user_id' => $user_id,
            'subscribed_currency_name' =>   $data1[ "subscribed_currency_name"]   
        ]);

        $this->assertDatabaseHas('subscribed_currencies', [
            'user_id' => $user_id,
            'subscribed_currency_name' =>   $data2[ "subscribed_currency_name"]   
        ]);

        $response = $this->withHeaders( ['Accept'=>'application/json','Authorization' =>  $JWT_token])->delete( '/api/user/subscribed_currencies' );
        $response->assertStatus(204);

        $this->assertDatabaseMissing('subscribed_currencies', [
            'user_id' => $user_id,
            'subscribed_currency_name' =>   $data1[ "subscribed_currency_name"]   
        ]);
        $this->assertDatabaseMissing('subscribed_currencies', [
            'user_id' => $user_id,
            'subscribed_currency_name' =>   $data2[ "subscribed_currency_name"]   
        ]);
    }

   
}
