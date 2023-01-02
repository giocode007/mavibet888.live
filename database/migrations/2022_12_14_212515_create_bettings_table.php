<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bettings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();  
            $table->integer('fight_id');  
            $table->string('role_type');  
            $table->float('amount');
            $table->string('bet_type');
            $table->string('bet_date_time');
            $table->string('status');
            $table->string('result')->nullable();
            $table->string('result_date_time')->nullable();
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
        Schema::dropIfExists('bettings');
    }
}
