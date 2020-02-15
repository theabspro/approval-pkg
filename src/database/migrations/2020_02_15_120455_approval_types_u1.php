<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalTypesU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('approval_types', function (Blueprint $table) {
			$table->unsignedInteger('created_by_id')->nullable()->after('filter_field');
			$table->unsignedInteger('updated_by_id')->nullable()->after('created_by_id');
			$table->unsignedInteger('deleted_by_id')->nullable()->after('updated_by_id');
			$table->timestamps();
			$table->softdeletes();

			$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('approval_types', function (Blueprint $table) {
			$table->dropForeign('approval_types_created_by_id_foreign');
			$table->dropForeign('approval_types_updated_by_id_foreign');
			$table->dropForeign('approval_types_deleted_by_id_foreign');

			$table->dropColumn('created_by_id');
			$table->dropColumn('updated_by_id');
			$table->dropColumn('deleted_by_id');
			$table->dropColumn('timestamps');
			$table->dropColumn('softdeletes');
		});
	}
}
