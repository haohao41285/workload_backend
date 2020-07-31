<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskLogsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('task_logs', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->integer('id_task_detail');
			$table->tinyInteger('time_work_per_day');
			$table->text('comment')->nullable();
			$table->dateTime('date_work');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('task_logs');
	}
}
