<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskDetailsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('task_details', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->integer('id_task');
			$table->integer('user_id');
			$table->tinyInteger('status')->comment('1: New,2: Progressing,3: Done, 4: Reopen')->default(0);
			$table->text('note')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('task_details');
	}
}
