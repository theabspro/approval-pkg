<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalTypesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('approval_types', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 191);
			$table->string('code', 191);
			$table->string('filter_field', 255);

			$table->unique(["name"]);
			$table->unique(["code"]);

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('approval_types');
	}
}
