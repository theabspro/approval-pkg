<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalTypeApprovalLevel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_type_approval_level', function (Blueprint $table) {

            $table->unsignedInteger('approval_type_id');
            $table->unsignedInteger('approval_level_id');
            $table->unsignedTinyInteger('approval_order');
            $table->unsignedInteger('current_status_id');
            $table->unsignedInteger('next_status_id');
            $table->unsignedInteger('reject_status_id')->nullable();
            $table->boolean('has_email_noty')->default(0);
            $table->boolean('has_sms_noty')->default(0);

            $table->foreign('approval_type_id')->references('id')->on('approval_types')->onDelete('CASCADE')->onUpdate('cascade');
            $table->foreign('approval_level_id')->references('id')->on('approval_levels')->onDelete('CASCADE')->onUpdate('cascade');

            $table->foreign('current_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');
            $table->foreign('next_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');
            $table->foreign('reject_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');


            $table->unique(["approval_type_id", "approval_level_id"],'atal_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_type_approval_level');
    }
}
