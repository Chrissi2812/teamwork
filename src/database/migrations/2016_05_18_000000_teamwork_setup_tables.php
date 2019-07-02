<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TeamworkSetupTables extends Migration
{
    private $useBigInt = false;

    public function __construct()
    {
        $this->useBigInt = version_compare(App::VERSION(), '5.8.0', '>=');
    }

	/**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( \Config::get( 'teamwork.users_table' ), function ( Blueprint $table )
        {
            // uncomment next line if you have upgraded to laravel 5.8
            // $table->bigIncrements( 'id' )->unsigned()->nullable()->change();
            $table->{$this->useBigInt ? 'bigInteger' : 'integer'}( 'current_team_id' )->unsigned()->nullable();
        } );


        Schema::create( \Config::get( 'teamwork.teams_table' ), function ( Blueprint $table )
        {
            $table->{$this->useBigInt ? 'bigIncrements' : 'increments'}( 'id' )->unsigned();
            $table->{$this->useBigInt ? 'bigInteger' : 'integer'}( 'owner_id' )->unsigned()->nullable();
            $table->string( 'name' );
            $table->timestamps();
        } );

        Schema::create( \Config::get( 'teamwork.team_user_table' ), function ( Blueprint $table )
        {
            $table->{$this->useBigInt ? 'bigIncrements' : 'increments'}( 'user_id' )->unsigned();
            $table->{$this->useBigInt ? 'bigIncrements' : 'increments'}( 'team_id' )->unsigned();
            $table->timestamps();

            $table->foreign( 'user_id' )
                ->references( \Config::get( 'teamwork.user_foreign_key' ) )
                ->on( \Config::get( 'teamwork.users_table' ) )
                ->onUpdate( 'cascade' )
                ->onDelete( 'cascade' );

            $table->foreign( 'team_id' )
                ->references( 'id' )
                ->on( \Config::get( 'teamwork.teams_table' ) )
                ->onDelete( 'cascade' );
        } );

        Schema::create( \Config::get( 'teamwork.team_invites_table' ), function(Blueprint $table)
        {
            $table->{$this->useBigInt ? 'bigIncrements' : 'increments'}('id');
            $table->{$this->useBigInt ? 'bigIncrements' : 'increments'}('user_id')->unsigned();
            $table->{$this->useBigInt ? 'bigIncrements' : 'increments'}('team_id')->unsigned();
            $table->enum('type', ['invite', 'request']);
            $table->string('email');
            $table->string('accept_token');
            $table->string('deny_token');
            $table->timestamps();
            $table->foreign( 'team_id' )
                ->references( 'id' )
                ->on( \Config::get( 'teamwork.teams_table' ) )
                ->onDelete( 'cascade' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\Config::get( 'teamwork.users_table' ), function(Blueprint $table)
        {
            $table->dropColumn('current_team_id');
        });

        Schema::table(\Config::get('teamwork.team_user_table'), function (Blueprint $table) {
            $table->dropForeign(\Config::get('teamwork.team_user_table').'_user_id_foreign');
            $table->dropForeign(\Config::get('teamwork.team_user_table').'_team_id_foreign');
        });

        Schema::drop(\Config::get('teamwork.team_user_table'));
        Schema::drop(\Config::get('teamwork.team_invites_table'));
        Schema::drop(\Config::get('teamwork.teams_table'));

    }
}
