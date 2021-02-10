<?php

namespace Abs\ApprovalPkg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalLevelMail extends Model {
	use SoftDeletes;
	protected $table = 'approval_level_mails';
}
