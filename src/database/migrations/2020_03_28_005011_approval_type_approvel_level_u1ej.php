<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalTypeApprovelLevelU1ej extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('approval_types', function (Blueprint $table) {
			$table->dropUnique('approval_types_name_unique');
			$table->dropUnique('approval_types_code_unique');
			$table->dropUnique('approval_types_company_id_name_unique');
			$table->dropUnique('approval_types_company_id_code_unique');

			$table->dropForeign('approval_types_company_id_foreign');

			$table->dropColumn('filter_field');

			$table->unsignedInteger('entity_id')->nullable()->after('company_id');

			$table->foreign('entity_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('CASCADE')->onUpdate('cascade');

			$table->unique(["company_id", "entity_id", "name"]);
			$table->unique(["company_id", "entity_id", "code"]);
		});

		DB::statement('DELETE FROM `approval_type_approval_level` WHERE 1');
		Schema::table('approval_type_approval_level', function (Blueprint $table) {
			$table->dropForeign('approval_type_approval_level_current_status_id_foreign');
			$table->dropForeign('approval_type_approval_level_next_status_id_foreign');
			$table->dropForeign('approval_type_approval_level_reject_status_id_foreign');

			$table->foreign('current_status_id')->references('id')->on('entity_statuses')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('next_status_id')->references('id')->on('entity_statuses')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('reject_status_id')->references('id')->on('entity_statuses')->onDelete('CASCADE')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
	}
}
