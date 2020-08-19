<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolePermissionsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('role_permissions', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->integer('id_role')->comment('id from roles table');
			$table->text('permissions')->comment('format json with id from permissions table')->nullable();
			$table->boolean('active')->default(1);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('role_permissions');
	}
}
