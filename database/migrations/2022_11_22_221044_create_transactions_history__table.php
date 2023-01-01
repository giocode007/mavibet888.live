<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); 
            $table->integer('betting_id')->nullable();
            $table->string('transaction_type');  
            $table->float('amount');
            $table->float('current_balance')->nullable();
            $table->float('current_commission')->nullable();
            $table->string('status');
            $table->string('note');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('request_date_time')->nullable();
            $table->string('approved_date_time')->nullable();
            $table->string('approve_by')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
