<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalLevelU23112020 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('approval_levels', function (Blueprint $table) {
			$table->boolean('has_verification_flow')->default(0)->after('approval_order');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('approval_levels', function (Blueprint $table) {
			$table->dropColumn('has_verification_flow');
		});
	}
}
