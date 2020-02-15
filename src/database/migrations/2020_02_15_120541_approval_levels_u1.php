<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalLevelsU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('approval_levels', function (Blueprint $table) {
			$table->boolean('has_email_noty')->default(0)->after('next_status_id');
			$table->boolean('has_sms_noty')->default(0)->after('has_email_noty');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('approval_levels', function (Blueprint $table) {
			$table->dropColumn('has_email_noty');
			$table->dropColumn('has_sms_noty');
		});
	}
}
