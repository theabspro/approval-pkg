<?php

namespace Abs\ApprovalPkg;
use Abs\ApprovalPkg\ApprovalLevel;
use Abs\ApprovalPkg\ApprovalType;
use Abs\ApprovalPkg\ApprovalTypeStatus;
use App\Http\Controllers\Controller;
use Auth;
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
			->leftJoin('approval_levels', 'approval_levels.approval_type_id', 'approval_types.id')
			->select(
				'approval_types.id',
				'approval_types.name as approval_type_name',
				'approval_types.code as approval_type_code',
				'approval_types.filter_field',
				DB::raw('count(distinct approval_levels.id) as no_of_levels'),
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
			->orderby('approval_types.id', 'desc');
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
					<a href="#!/approval-pkg/approval-type/view/' . $approval_types->id . '">
	                        <img class="img-responsive" src="' . $view_img . '" alt="View" />
	                    	</a>
					<a href="#!/approval-pkg/approval-type/edit/' . $approval_types->id . '">
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
			$approval_type->approval_type_statuses = [];
			$action = 'Add';
		} else {
			$approval_type = ApprovalType::withTrashed()->where('id', $id)->with([
				'approvalTypeStatuses',
			])
				->first();
			$action = 'Edit';
		}
		$this->data['approval_type'] = $approval_type;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveApprovalType(Request $request) {
		// dd($request->all());
		DB::beginTransaction();
		try {

			$error_messages = [
				'name.required' => 'Approval Type name is required',
				'name.unique' => 'Approval Type name is already taken',
				'code.required' => 'Approval Type code is required',
				'code.unique' => 'Approval Type code is already taken',
				'filter_field.required' => 'Filter Field is required',
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
				'filter_field' => 'required',
			], $error_messages);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			//VALIDATE UNIQUE FOR APPROVAL-TYPE-STATUSES
			if (isset($request->approval_type_statuses) && !empty($request->approval_type_statuses)) {
				$error_messages_1 = [
					'status.required' => 'Approval Type Status is required',
					'status.unique' => 'Approval Type Status is already taken',
				];

				foreach ($request->approval_type_statuses as $approval_type_status_key => $approval_type_status) {
					$validator_1 = Validator::make($approval_type_status, [
						'status' => [
							'unique:approval_type_statuses,status,' . $approval_type_status['id'] . ',id,approval_type_id,' . $approval_type_status['approval_type_id'],
							'required',
						],
					], $error_messages_1);

					if ($validator_1->fails()) {
						return response()->json(['success' => false, 'errors' => $validator_1->errors()->all()]);
					}

					//FIND DUPLICATE APPROVAL-TYPE-STATUSES
					foreach ($request->approval_type_statuses as $search_key => $search_array) {
						if ($search_array['status'] == $approval_type_status['status']) {
							if ($search_key != $approval_type_status_key) {
								return response()->json(['success' => false, 'errors' => ['Approval Type Status is already taken']]);
							}
						}
					}
				}
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

			//DELETE APPROVAL-TYPE-STATUSES
			if (!empty($request->approval_type_status_removal_ids)) {
				$approval_type_status_removal_ids = json_decode($request->approval_type_status_removal_ids, true);
				ApprovalTypeStatus::withTrashed()->whereIn('id', $approval_type_status_removal_ids)->forcedelete();
			}

			if (isset($request->approval_type_statuses) && !empty($request->approval_type_statuses)) {
				foreach ($request->approval_type_statuses as $key => $approval_type_status) {
					$approval_status = ApprovalTypeStatus::withTrashed()->firstOrNew(['id' => $approval_type_status['id']]);
					$approval_status->fill($approval_type_status);
					$approval_status->approval_type_id = $approval_type->id;
					if ($approval_type_status['switch_value'] == 'Active') {
						$approval_status->deleted_at = NULL;
						$approval_status->deleted_by_id = NULL;
					} else {
						$approval_status->deleted_at = date('Y-m-d H:i:s');
						$approval_status->deleted_by_id = Auth::user()->id;
					}
					if (empty($approval_type_status['id'])) {
						$approval_status->created_by_id = Auth::user()->id;
						$approval_status->created_at = date('Y-m-d H:i:s');
						$approval_status->updated_at = NULL;
					} else {
						$approval_status->updated_by_id = Auth::user()->id;
						$approval_status->updated_at = date('Y-m-d H:i:s');
					}
					$approval_status->save();
				}
			}

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
			ApprovalType::withTrashed()->where('id', $request->id)->forceDelete();

			DB::commit();
			return response()->json(['success' => true, 'message' => 'Approval Type Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function viewApprovalType(Request $r) {
		$id = $r->id;
		if ($id) {
			$this->data['approval_type'] = ApprovalType::withTrashed()->where('id', $id)->with([
				'approvalTypeStatuses',
				'approvalLevels',
			])
				->first();
			$this->data['action'] = 'View';
			$this->data['extras'] = [
				'approval_type_status_list' => collect(ApprovalTypeStatus::where('approval_type_id', $id)->select('status', 'id')->get())->prepend(['status' => 'Select Approval Type Status']),
			];
		} else {
			return response()->json(['success' => false, 'error' => 'Approval Type ID not found']);
		}
		return response()->json($this->data);
	}

	public function saveApprovalLevel(Request $request) {
		//dd($request->all());
		try {
			if (isset($request->approval_levels) && !empty($request->approval_levels)) {
				$error_messages = [
					'name.required' => 'Approval Level name is required',
					'name.unique' => 'Approval Level name is already taken',
					'approval_order.required' => 'Approval Order is required',
					'approval_order.unique' => 'Approval Order is already taken',
				];

				foreach ($request->approval_levels as $approval_level_key => $approval_level) {
					$validator = Validator::make($approval_level, [
						'name' => [
							'unique:approval_levels,name,' . $approval_level['id'] . ',id,approval_type_id,' . $approval_level['approval_type_id'],
							'required:true',
						],
						'approval_order' => [
							'unique:approval_levels,approval_order,' . $approval_level['id'] . ',id,approval_type_id,' . $approval_level['approval_type_id'],
							'required:true',
						],
						'current_status_id' => 'required',
						'next_status_id' => 'required',
						'reject_status_id' => 'required',
						'has_email_noty' => 'required',
						'has_sms_noty' => 'required',
					], $error_messages);

					if ($validator->fails()) {
						return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
					}

					//FIND DUPLICATE APPROVAL-LEVELS
					foreach ($request->approval_levels as $search_key => $search_array) {
						if ($search_array['name'] == $approval_level['name']) {
							if ($search_key != $approval_level_key) {
								return response()->json(['success' => false, 'errors' => ['Approval Level name is already taken']]);
							}
						}
						if ($search_array['approval_order'] == $approval_level['approval_order']) {
							if ($search_key != $approval_level_key) {
								return response()->json(['success' => false, 'errors' => ['Approval Order is already taken']]);
							}
						}
					}
				}

				//DELETE APPROVAL-LEVELS
				DB::beginTransaction();
				if (!empty($request->approval_level_removal_ids)) {
					$approval_level_removal_ids = json_decode($request->approval_level_removal_ids, true);
					$approval_level_delete = ApprovalLevel::withTrashed()->whereIn('id', $approval_level_removal_ids)->forcedelete();
				}

				foreach ($request->approval_levels as $key => $approval_level) {
					$approval_level_save = ApprovalLevel::withTrashed()->firstOrNew(['id' => $approval_level['id']]);
					$approval_level_save->fill($approval_level);
					// has email noty?
					if ($approval_level['has_email_noty'] == 'Yes') {
						$approval_level_save->has_email_noty = 1;
					} else {
						$approval_level_save->has_email_noty = 0;
					}
					// has sms noty?
					if ($approval_level['has_sms_noty'] == 'Yes') {
						$approval_level_save->has_sms_noty = 1;
					} else {
						$approval_level_save->has_sms_noty = 0;
					}
					// active status
					if ($approval_level['switch_value'] == 'Active') {
						$approval_level_save->deleted_at = NULL;
						$approval_level_save->deleted_by_id = NULL;
					} else {
						$approval_level_save->deleted_at = date('Y-m-d H:i:s');
						$approval_level_save->deleted_by_id = Auth::user()->id;
					}
					if (empty($approval_level['id'])) {
						$msg = "Saved";
						$approval_level_save->created_by_id = Auth()->user()->id;
						$approval_level_save->created_at = date('Y-m-d H:i:s');
						$approval_level_save->updated_by_id = NULL;
						$approval_level_save->updated_at = NULL;
					} else {
						$msg = "Updated";
						$approval_level_save->updated_by_id = Auth()->user()->id;
						$approval_level_save->updated_at = date('Y-m-d H:i:s');
					}
					$approval_level_save->save();
				}
				DB::commit();
				return response()->json(['success' => true, 'comes_from' => $msg]);
			} else {
				if (!empty($request->approval_level_removal_ids)) {
					$approval_level_removal_ids = json_decode($request->approval_level_removal_ids, true);
					$approval_level_delete = ApprovalLevel::withTrashed()->whereIn('id', $approval_level_removal_ids)->forcedelete();
					$msg = "Updated";
					return response()->json(['success' => true, 'comes_from' => $msg]);
				}
				return response()->json(['success' => true, 'comes_from' => '']);
			}

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function getApprovalStatus(Request $request) {
		return ApprovalLevel::getApprovalLevelList($request);
	}
}
