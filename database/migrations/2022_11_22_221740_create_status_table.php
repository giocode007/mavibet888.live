<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('status')){
            Schema::create('status', function (Blueprint $table) {
                $table->id();
                $table->string('status_type')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }
        

        // DB::table('status')->insert([
        //     ['status_type' => 'BETTING IS NOW OPEN.'],
        //     ['status_type' => 'LAST CALL TO PLACE YOUR BETS.'],
        //     ['status_type' => 'PLEASE STANDBY. WE ARE FIXING YOUR BETS.'],
        //     ['status_type' => 'PLEASE ALWAYS REFRESH YOUR VIDEO.'],
        //     ['status_type' => 'STANDBY FOR OUR VIDEO.'],
        //     ['status_type' => 'THIS IS OUR LAST FIGHT.'],
        //     ['status_type' => 'Payout with 120 and below shall be cancelled.'],
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status');
    }
}
