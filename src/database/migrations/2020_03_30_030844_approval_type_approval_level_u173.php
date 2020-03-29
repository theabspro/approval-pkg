<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalTypeApprovalLevelU173 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('approval_type_approval_level', function (Blueprint $table) {
			$table->dropForeign('approval_type_approval_level_current_status_id_foreign');
			$table->dropForeign('approval_type_approval_level_next_status_id_foreign');
			$table->dropForeign('approval_type_approval_level_reject_status_id_foreign');

			$table->dropColumn('approval_order');
			$table->dropColumn('current_status_id');
			$table->dropColumn('next_status_id');
			$table->dropColumn('reject_status_id');
			$table->dropColumn('has_email_noty');
			$table->dropColumn('has_sms_noty');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('approval_type_approval_level', function (Blueprint $table) {
			$table->unsignedMediumInteger('approval_order')->after('approval_level_id');
			$table->unsignedInteger('current_status_id')->after('approval_order');
			$table->unsignedInteger('next_status_id')->after('current_status_id');
			$table->unsignedInteger('reject_status_id')->after('next_status_id');
			$table->boolean('has_email_noty')->after('reject_status_id');
			$table->boolean('has_sms_noty')->after('has_email_noty');

			$table->foreign('current_status_id')->references('id')->on('entity_statuses')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('next_status_id')->references('id')->on('entity_statuses')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('reject_status_id')->references('id')->on('entity_statuses')->onDelete('CASCADE')->onUpdate('cascade');
		});
	}
}
