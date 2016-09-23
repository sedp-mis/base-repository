<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTargetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('targets', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('spy_id')->nullable();
			$table->string('name');
			$table->timestamps();

			$table->foreign('spy_id')->references('id')->on('spies');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('targets');
	}

}
