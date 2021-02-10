<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalLevelMails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('approval_level_mails')) {
            Schema::create('approval_level_mails', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('approval_level_id');
                $table->unsignedInteger('to_entity_id')->nullable();
                $table->unsignedInteger('cc_entity_id')->nullable();
                $table->unsignedInteger('created_by_id')->nullable();
                $table->unsignedInteger('updated_by_id')->nullable();
                $table->unsignedInteger('deleted_by_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('approval_level_id')->references('id')->on('approval_levels')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('updated_by_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            });
        }
        if (!Schema::hasColumn('approval_levels', 'mail_type_id')) {
            Schema::table('approval_levels', function (Blueprint $table) {
                $table->unsignedInteger('mail_type_id')->nullable()->after('has_sms_noty');

                $table->foreign('mail_type_id')->references('id')->on('configs')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('approval_levels', 'mail_type_id')) {
            Schema::table('approval_levels', function (Blueprint $table) {
                $table->dropForeign('approval_levels_mail_type_id_foreign');
                $table->dropColumn('mail_type_id');
            });
        }
        Schema::dropIfExists('approval_level_mails');
    }
}
