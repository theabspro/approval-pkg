<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalTypesU2 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('approval_types', function (Blueprint $table) {
			$table->unsignedInteger('company_id')->default(1)->after('id');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
			$table->unique(["company_id", "name"]);
			$table->unique(["company_id", "code"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('approval_types', function (Blueprint $table) {
			$table->dropForeign('approval_types_company_id_foreign');
			$table->dropUnique("approval_types_company_id_name_unique");
			$table->dropUnique("approval_types_company_id_code_unique");
			$table->dropColumn('company_id');
		});
	}
}
