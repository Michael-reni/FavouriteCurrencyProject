<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserAuthController extends Controller
{

    /**
     * @OA\Post(
     *      path="/user/register",
     *      tags={"User"},
     *      summary="Register new user",
     *     
     *      @OA\RequestBody(     
     *           required=true,
     *           description="pass data to create user record",
        *      @OA\JsonContent(
        *           @OA\Property(property = "name", type="string", example="Jan", description = "user name"),
        *           @OA\Property(property = "email", type="string", example="jankowalski@wp.pl", description = "user email"),
        *           @OA\Property(property = "password", type="string", example="strongpassword123", description = "user password"),
        *      )
     *      ),
     * 
     *      @OA\Response(
     *          response=201,
     *          description="Created",
     *      @OA\JsonContent(),
     *          
     *       ),
  
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
    public function register(Request $request){
       
        $validated = $request->validate([      
            'name'=>'required|string',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:9'
        ]);

        try{
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
            ]);
            $JWT_token = $user->createToken('authToken')->plainTextToken;

            //return response()->json(['message' => 'User successfully crated',],201);

            return response()->json([ 
                'message' => 'User successfully crated',    
                'access_token' => 'Bearer '.$JWT_token,
                'user_name' => $user->name,
                'user_email' => $user->email
            ],201);
        }
        catch(\Exception $exception){
            Log::channel('userAuth')->info(now()->toDateTimeString() . ' something unexpected happend in: UserAuthController ' . $exception->getMessage());      
            return response()->json($exception->getMessage(),500);
        }
       

       
    }

    /**
     * @OA\Post(
     *      path="/user/login",
    *      tags={"User"},
     *      summary="Login user",
      *      @OA\RequestBody(     
     *           required=true,
     *           description="pass data to Login user and get JWT token",
        *      @OA\JsonContent(
        *           @OA\Property(property = "email", type="string", example="jankowalski@wp.pl", description = "user email"),
        *           @OA\Property(property = "password", type="string", example="strongpassword123", description = "user password"),
        *      )
     *      ),
     * 
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      @OA\JsonContent(),
     *          
     *       ),
     *  *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * 
     *        @OA\Response(
     *          response=422,
     *          description="Unprocessable entity",
     *      ),
     * 
     *       @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *      ),
     *     
     *     )
     */

    public function login(Request $request){
        
        $validated = $request->validate([           
            'email'=>'required|email',
            'password'=>'required'
        ]);

        try{
            if (!\Auth::attempt($request->only('email', 'password'))) {
                return response()->json(['message' => 'Wrong credentials'],401);
            }

            $user = User::where('email', $request['email'])->firstOrFail();
            $JWT_token = $user->createToken('authToken')->plainTextToken;

            return response()->json([ 
                'message' => 'User logged successfully',    
                'access_token' => 'Bearer '.$JWT_token,
                'user_name' => $user->name,
                'user_email' => $user->email
            ],200);
        }
        catch(\Exception $exception){
            Log::channel('userAuth')->info(now()->toDateTimeString() . ' something unexpected happend in: UserAuthController ' . $exception->getMessage());
            return response()->json($exception->getMessage(),500);
        }
        
     }


  /**
     * @OA\Get(
     *      path="/user",
     *      tags={"User"},
     *      security={ {"sanctum": {} }},
     *      summary="view user",
     *     
     
     * 
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      @OA\JsonContent(),
     *          
     *       ),
     *  *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * 
   
     * 
     * 
     *       @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *      ),
     *     
     *     )
     */
     public function user(Request $request){

     }
    


    
}
