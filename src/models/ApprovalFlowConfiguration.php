<?php

namespace Abs\ApprovalPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalFlowConfiguration extends BaseModel {
	use SoftDeletes;
	use SeederTrait;
	protected $table = 'approval_flow_configurations';
	protected $fillable = [
		'name',
		'approval_type_id',
		'approval_level_id',
		'value',
		'next_status_id',
	];
	protected $appends = ['switch_value'];

	public function getSwitchValueAttribute() {
		return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	}

}
