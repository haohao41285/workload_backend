<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetaiProgressingTasksTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('detai_progressing_tasks', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->integer('id_task_detail');
			$table->string('title');
			$table->integer('user_id');
			$table->boolean('status')->comment('0: New, 1: Done')->default(0);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('detai_progressing_tasks');
	}
}
