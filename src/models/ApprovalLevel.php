<?php

namespace Abs\ApprovalPkg;

use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalLevel extends Model {
	use SoftDeletes;
	protected $table = 'approval_levels';
	protected $fillable = [
		'name',
		'category_id',
		'approval_order',
		'has_verification_flow',
		'current_status_id',
		'next_status_id',
		'reject_status_id',
		'has_email_noty',
		'has_sms_noty',
	];
	protected $appends = ['switch_value'];

	public function getSwitchValueAttribute() {
		return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	}

	public function entity() {
		return $this->belongsTo('App\Config', 'category_id');
	}

	public function currentStatus() {
		return $this->belongsTo('Abs\ApprovalPkg\EntityStatus', 'current_status_id');
	}

	public function nextStatus() {
		return $this->belongsTo('Abs\ApprovalPkg\EntityStatus', 'next_status_id');
	}

	public function rejectedStatus() {
		return $this->belongsTo('Abs\ApprovalPkg\EntityStatus', 'reject_status_id');
	}

	public static function createFromObject($record_data) {

		$errors = [];
		$company = Company::where('code', $record_data->company)->first();
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$type = Config::where('name', $record_data->type)->where('config_type_id', 89)->first();
		if (!$type) {
			$errors[] = 'Invalid Tax Type : ' . $record_data->type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->tax_name,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

	public static function createFromCollection($records) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company) {
					continue;
				}
				$record = self::createFromObject($record_data);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}

}
