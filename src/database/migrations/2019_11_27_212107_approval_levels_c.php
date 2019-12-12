<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApprovalLevelsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('approval_levels', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('approval_type_id');
			$table->string('name', 191);
			$table->unsignedTinyInteger('approval_order');
			$table->unsignedInteger('current_status_id');
			$table->unsignedInteger('next_status_id');
			// $table->unsignedInteger('reject_status_id');
			$table->unsignedInteger('created_by_id')->nullable();
			$table->unsignedInteger('updated_by_id')->nullable();
			$table->unsignedInteger('deleted_by_id')->nullable();
			$table->timestamps();
			$table->softdeletes();

			$table->foreign('approval_type_id')->references('id')->on('approval_types')->onDelete('CASCADE')->onUpdate('cascade');

			$table->foreign('current_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('next_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');
			// $table->foreign('reject_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');
			$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

			$table->unique(["approval_type_id", "name"]);
			$table->unique(["approval_type_id", "approval_order"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('approval_levels');
	}
}
