<?php

namespace Abs\ApprovalPkg;

use Abs\ApprovalPkg\ApprovalLevel;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalType extends Model {
	use SoftDeletes;
	protected $table = 'approval_types';
	public $timestamps = true;
	protected $fillable = [
		'name',
		'code',
		'filter_field',
	];

	// public function approvalLevels() {
	// 	// return $this->hasMany('Abs\ApprovalPkg\ApprovalLevel', 'approval_type_id', 'id')->withTrashed()->orderby('id');
	// }

	public function approvalLevels() {
		return $this->belongsToMany('Abs\ApprovalPkg\ApprovalLevel', 'approval_type_approval_level', 'approval_type_id', 'approval_level_id')->withPivot(['approval_order', 'current_status_id', 'next_status_id','reject_status_id','has_email_noty','has_sms_noty']);
	}

	public function approvalTypeStatuses() {
		return $this->hasMany('Abs\ApprovalPkg\ApprovalTypeStatus', 'approval_type_id', 'id')->withTrashed()->orderby('id');
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
