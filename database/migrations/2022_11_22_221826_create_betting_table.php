<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('betting', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->float('amount');
            $table->string('bet_type');
            $table->string('bet_date_time');
            $table->string('status');
            $table->string('result');
            $table->string('result_date_time');
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
        Schema::dropIfExists('betting');
    }
}
