<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalTypeStatusesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('approval_type_statuses', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('approval_type_id');
			$table->string('status', 191);

			$table->foreign('approval_type_id')->references('id')->on('approval_types')->onDelete('CASCADE')->onUpdate('cascade');

			$table->unique(["approval_type_id", "status"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('approval_type_statuses');
	}
}
