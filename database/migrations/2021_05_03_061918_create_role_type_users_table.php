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
        Schema::create('role_type_users', function (Blueprint $table) {
            $table->id();
            $table->string('role_type')->nullable();
            $table->timestamps();
        });

        DB::table('role_type_users')->insert([
            ['role_type' => 'Admin'],
            ['role_type' => 'Operator'],
            ['role_type' => 'Declarator'],
            ['role_type' => 'Sub_Operator'],
            ['role_type' => 'Master_Agent'],
            ['role_type' => 'Gold_Agent'],
            ['role_type' => 'Player']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_type_users');
    }
}
