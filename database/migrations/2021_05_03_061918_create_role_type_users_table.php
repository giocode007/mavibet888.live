<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleTypeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_type', function (Blueprint $table) {
            $table->id();
            $table->string('role_name')->nullable();
            $table->timestamps();
        });

        DB::table('role_type')->insert([
            ['role_name' => 'Admin'],
            ['role_name' => 'Operator'],
            ['role_name' => 'Declarator'],
            ['role_name' => 'Sub_Operator'],
            ['role_name' => 'Master_Agent'],
            ['role_name' => 'Gold_Agent'],
            ['role_name' => 'Player']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_type');
    }
}
