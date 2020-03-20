<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterApprovalLvels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approval_levels', function (Blueprint $table) {

            $table->dropForeign('approval_levels_current_status_id_foreign');
            $table->dropForeign('approval_levels_next_status_id_foreign');
            $table->dropForeign('approval_levels_reject_status_id_foreign');
            $table->dropForeign('approval_levels_approval_type_id_foreign');

            $table->dropUnique('approval_levels_approval_type_id_name_unique');
            $table->dropUnique('approval_levels_approval_type_id_approval_order_unique');

            $table->dropColumn('has_email_noty');
            $table->dropColumn('has_sms_noty');
            $table->dropColumn('current_status_id');
            $table->dropColumn('next_status_id');
            $table->dropColumn('reject_status_id');
            $table->dropColumn('approval_type_id');
            $table->dropColumn('approval_order');

            $table->unsignedInteger('category_id')->nullable()->after('id');
            $table->unique(["category_id", "name"]);
            $table->foreign('category_id')->references('id')->on('configs')->onDelete('SET NULL')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approval_levels', function (Blueprint $table) {

            $table->dropForeign('approval_levels_category_id_foreign');

            $table->dropUnique('approval_levels_category_id_name_unique');

            $table->dropColumn('category_id');

            $table->unsignedInteger('approval_type_id')->after('id');
            $table->unsignedTinyInteger('approval_order')->after('name');
            $table->unsignedInteger('current_status_id')->after('approval_order');
            $table->unsignedInteger('next_status_id')->after('current_status_id');
            $table->boolean('has_email_noty')->default(0)->after('next_status_id');
            $table->boolean('has_sms_noty')->default(0)->after('has_email_noty');
            $table->unsignedInteger('reject_status_id')->nullable()->after('next_status_id');

            $table->foreign('reject_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');
            $table->foreign('approval_type_id')->references('id')->on('approval_types')->onDelete('CASCADE')->onUpdate('cascade');
            $table->foreign('current_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');
            $table->foreign('next_status_id')->references('id')->on('approval_type_statuses')->onDelete('CASCADE')->onUpdate('cascade');

            $table->unique(["approval_type_id", "name"]);
            $table->unique(["approval_type_id", "approval_order"]);

        
        });
    }
}
