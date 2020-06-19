<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EntityStatusesU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('entity_statuses', function (Blueprint $table) {
			$table->string('code', 64)->nullable()->after('entity_id');
			$table->unique(["company_id", "entity_id", "code"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('entity_statuses', function (Blueprint $table) {
			$table->dropUnique('entity_statuses_company_id_entity_id_code_unique');
			$table->dropColumn('code');
		});
	}
}
