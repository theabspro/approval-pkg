<?php

namespace Abs\ApprovalPkg;

use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalTypeStatus extends Model {
	use SoftDeletes;
	protected $table = 'approval_type_statuses';
	public $timestamps = true;
	protected $fillable = [
		'approval_type_id',
		'status',
	];
	// protected $appends = ['switch_value'];

	// public function getSwitchValueAttribute() {
	// 	return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	// }

	public static function getApprovalTypeStatusList($request) {
		$data['approval_type_status_list'] = self::select(
			'id',
			'status'
		)
			->where('approval_type_id', $request->id)
			->orderBy('id', 'ASC')
			->get();
		return response()->json($data);
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
