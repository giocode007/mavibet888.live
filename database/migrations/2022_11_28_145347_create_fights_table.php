<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fights', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('event_id')->constrained();
            $table->string('fight_number')->nullable();
            $table->string('result')->nullable();
            $table->string('payoutMeron')->nullable();
            $table->string('payoutWala')->nullable();
            $table->string('isOpen');
            $table->string('status');
            $table->string('declared_by')->nullable();
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
        Schema::dropIfExists('fights');
    }
}
