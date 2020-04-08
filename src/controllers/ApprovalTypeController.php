<?php

namespace Abs\ApprovalPkg;
use Abs\ApprovalPkg\ApprovalLevel;
use Abs\ApprovalPkg\ApprovalType;
use Abs\ApprovalPkg\ApprovalTypeStatus;
use App\ActivityLog;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ApprovalTypeController extends Controller {

	public function __construct() {
	}

	public function getApprovalTypeList(Request $request) {
		$approval_types = ApprovalType::withTrashed()
			->leftJoin('approval_type_statuses', 'approval_type_statuses.approval_type_id', 'approval_types.id')
			->leftJoin('approval_type_approval_level', 'approval_type_approval_level.approval_type_id', 'approval_types.id')
			->leftJoin('configs as e', 'e.id', 'approval_types.entity_id')
			->select(
				'approval_types.id',
				'approval_types.name as approval_type_name',
				'approval_types.code as approval_type_code',
				'e.name as entity_type',
				DB::raw('count(distinct approval_type_approval_level.approval_level_id) as no_of_levels'),
				DB::raw('count(distinct approval_type_statuses.id) as no_of_status'),
				DB::raw('IF(approval_types.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where('approval_types.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->approval_type_name)) {
					$query->where('approval_types.name', 'LIKE', '%' . $request->approval_type_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('approval_types.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('approval_types.deleted_at');
				}
			})
			->groupBy('approval_types.id')
		// ->orderby('approval_types.id', 'desc')
		;
		//dd($approval_types);
		return Datatables::of($approval_types)
			->addColumn('name', function ($approval_types) {
				$status = $approval_types->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $approval_types->approval_type_name;
			})
			->addColumn('action', function ($approval_types) {
				$view_img = asset('public/theme/img/table/cndn/view.svg');
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/approval-pkg/approval-type/view/' . $approval_types->id . '" title="View">
	                        <img class="img-responsive" src="' . $view_img . '" alt="View" />
	                    	</a>
					<a href="#!/approval-pkg/approval-type/edit/' . $approval_types->id . '" title="Edit">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete-approval-type"
					onclick="angular.element(this).scope().deleteApprovalType(' . $approval_types->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getApprovalTypeFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$approval_type = new ApprovalType;
			$approval_type->approval_levels = [];
			$this->data['extras'] = [];
			$action = 'Add';
		} else {
			$approval_type = ApprovalType::withTrashed()->where('id', $id)->with([
				'approvalLevels',
				'entityType',
			])
				->first();
			$action = 'Edit';
			$this->data['extras'] = [
				'approval_levels_list' => collect(ApprovalLevel::where('category_id', $approval_type->entity_id)->select('id', 'name')->get())->prepend(['name' => 'Select Level']),
			];
		}
		$this->data['entity_list'] = Collect(Config::getCategoryList()->prepend(['id' => '', 'name' => 'Select Entity']));
		$this->data['approval_type'] = $approval_type;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function getApprovalLevelsList(Request $request) {
		$this->data['extras'] = [
			'approval_levels_list' => collect(ApprovalLevel::where('category_id', $request->category_id)->select('id', 'name')->get())->prepend(['name' => 'Select Level']),
		];

		return response()->json($this->data);
	}

	public function saveApprovalType(Request $request) {
		// dd($request->all());
		DB::beginTransaction();
		try {

			$error_messages = [
				'name.required' => 'Verification Flow name is required',
				'name.unique' => 'Verification Flow name is already taken',
				'code.required' => 'Verification Flow code is required',
				'code.unique' => 'Verification Flow code is already taken',
				// 'filter_field.required' => 'Filter Field is required',
			];

			$validator = Validator::make($request->all(), [
				'name' => [
					'unique:approval_types,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
					'required',
				],
				'code' => [
					'unique:approval_types,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
					'required',
				],
				// 'filter_field' => 'required',
			], $error_messages);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			if (empty($request->id)) {
				$approval_type = new ApprovalType;
				$msg = "Saved";
				$approval_type->created_by_id = Auth()->user()->id;
				$approval_type->created_at = date('Y-m-d H:i:s');
				$approval_type->updated_at = NULL;
			} else {
				$approval_type = ApprovalType::withTrashed()->where('id', $request->id)->first();
				$msg = "Updated";
				$approval_type->updated_by_id = Auth()->user()->id;
				$approval_type->updated_at = date('Y-m-d H:i:s');
			}

			$approval_type->fill($request->all());
			$approval_type->company_id = Auth::user()->company_id;
			if ($request->status == 'Active') {
				$approval_type->deleted_at = NULL;
				$approval_type->deleted_by_id = NULL;
			} else {
				$approval_type->deleted_at = date('Y-m-d H:i:s');
				$approval_type->deleted_by_id = Auth::user()->id;
			}
			$approval_type->save();
			if (isset($request->approval_levels) && !empty($request->approval_levels)) {
				$approval_level_values = array_column($request->approval_levels, 'approval_level');
				$approval_level_count = count($approval_level_values);
				$spproval_level_unique_count = count(array_unique($approval_level_values));

				if ($approval_level_count != $spproval_level_unique_count) {
					return response()->json(['success' => false, 'errors' => ['Name is Already Taken!']]);
				}
				// DB::beginTransaction();
				// $approval_type = ApprovalType::find($request->id);

				$approval_level_ids = [];
				foreach ($request->approval_levels as $key => $approval_level) {
					$approval_level_ids[] = $approval_level['approval_level'];
				}
				$approval_type->approvalLevels()->sync($approval_level_ids);

				// $activity = new ActivityLog;
				// $activity->date_time = Carbon::now();
				// $activity->user_id = Auth::user()->id;
				// $activity->module = 'Verification Level Updated';
				// $activity->entity_id = $approval_type->id;
				// $activity->entity_type_id = 385;
				// $activity->activity_id = $request->id == NULL ? 280 : 281;
				// $activity->activity = $request->id == NULL ? 280 : 281;
				// $activity->details = json_encode($activity);
				// $activity->save();

				// DB::commit();
				// return response()->json(['success' => true, 'comes_from' => 'Added']);
			}
			$activity = new ActivityLog;
			$activity->date_time = Carbon::now();
			$activity->user_id = Auth::user()->id;
			$activity->module = 'Verification Flow';
			$activity->entity_id = $approval_type->id;
			$activity->entity_type_id = 385;
			$activity->activity_id = $request->id == NULL ? 280 : 281;
			$activity->activity = $request->id == NULL ? 280 : 281;
			$activity->details = json_encode($activity);
			$activity->save();

			DB::commit();
			return response()->json(['success' => true, 'comes_from' => $msg]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function deleteApprovalType(Request $request) {
		DB::beginTransaction();
		try {
			$approval_type = ApprovalType::withTrashed()->where('id', $request->id)->forceDelete();

			if ($approval_type) {
				$activity = new ActivityLog;
				$activity->date_time = Carbon::now();
				$activity->user_id = Auth::user()->id;
				$activity->module = 'Verification Flow';
				$activity->entity_id = $request->id;
				$activity->entity_type_id = 385;
				$activity->activity_id = 282;
				$activity->activity = 282;
				$activity->details = json_encode($activity);
				$activity->save();
			}

			DB::commit();
			return response()->json(['success' => true, 'message' => 'Verification Flow Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function viewApprovalType(Request $r) {
		$id = $r->id;
		if ($id) {
			$this->data['approval_type'] = $approval_type = ApprovalType::withTrashed()->where('id', $id)->with([
				'approvalLevels',
				'entityType',
			])
				->first();

			$this->data['action'] = 'View';
			$this->data['extras'] = [
				'approval_levels_list' => collect(ApprovalLevel::where('category_id', $approval_type->entity_id)->select('id', 'name')->get())->prepend(['name' => 'Select Level']),
			];
		} else {
			return response()->json(['success' => false, 'error' => 'Verification Flow ID not found']);
		}
		return response()->json($this->data);
	}

	// public function saveApprovalTypeLevel(Request $request) {
	// 	try {
	// 		if (isset($request->approval_levels) && !empty($request->approval_levels)) {
	// 			$approval_level_values = array_column($request->approval_levels, 'approval_level');
	// 			$approval_level_count = count($approval_level_values);
	// 			$spproval_level_unique_count = count(array_unique($approval_level_values));

	// 			if ($approval_level_count != $spproval_level_unique_count) {
	// 				return response()->json(['success' => false, 'errors' => ['Name is Already Taken!']]);
	// 			}
	// 			DB::beginTransaction();
	// 			$approval_type = ApprovalType::find($request->id);

	// 			$approval_level_ids = [];
	// 			foreach ($request->approval_levels as $key => $approval_level) {
	// 				$approval_level_ids[] = $approval_level['approval_level'];
	// 			}
	// 			$approval_type->approvalLevels()->sync($approval_level_ids);

	// 			$activity = new ActivityLog;
	// 			$activity->date_time = Carbon::now();
	// 			$activity->user_id = Auth::user()->id;
	// 			$activity->module = 'Verification Level Updated';
	// 			$activity->entity_id = $approval_type->id;
	// 			$activity->entity_type_id = 385;
	// 			$activity->activity_id = $request->id == NULL ? 280 : 281;
	// 			$activity->activity = $request->id == NULL ? 280 : 281;
	// 			$activity->details = json_encode($activity);
	// 			$activity->save();

	// 			DB::commit();
	// 			return response()->json(['success' => true, 'comes_from' => 'Added']);
	// 		}

	// 	} catch (Exception $e) {
	// 		DB::rollBack();
	// 		return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
	// 	}
	// }

	public function getApprovalStatus(Request $request) {
		return ApprovalTypeStatus::getApprovalTypeStatusList($request);
	}
}
