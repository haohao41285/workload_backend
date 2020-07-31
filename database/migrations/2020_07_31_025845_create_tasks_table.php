<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('tasks', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			$table->text('des')->nullable();
			$table->dateTime('date_start')->nullable();
			$table->dateTime('due')->nullable();
			$table->string('idList')->nullable();
			$table->string('progressing', 3)->nullable()->defalt('0');
			$table->tinyInteger('status')->comment("1: New, 2: Progressing, 3: Done, 4: Reopen")->default(1);
			$table->tinyInteger('time_work_total')->nullable()->default(0);

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('tasks');
	}
}
