<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
           
                //$table->uuid('id')->primary()->default(DB::raw("(uuid())")); // let postgres be responsible for making uuid's
                $table->string('name')->primary();
                $table->string('currency_code');
                $table->decimal('exchange_rate', $precision = 14, $scale = 8);  
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
