<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalLevelsU extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('approval_levels', function (Blueprint $table) {
			$table->unsignedInteger('reject_status_id')->after('next_status_id');
			$table->foreign('reject_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropForeign('approval_levels_reject_status_id_foreign');
		Schema::dropColumn('reject_status_id');
	}
}
