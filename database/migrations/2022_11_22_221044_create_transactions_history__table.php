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
        Schema::create('transactions_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();  
            $table->string('transaction_type');
            $table->float('amount');
            $table->string('status');
            $table->string('note');
            $table->string('from');
            $table->string('to');
            $table->string('request_date_time');
            $table->string('approved_date_time');
            $table->string('approve_by');
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
        Schema::dropIfExists('transactions_history');
    }
}
