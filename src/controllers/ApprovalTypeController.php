<?php

namespace Abs\ApprovalPkg;
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
				DB::raw('count(approval_levels.id) as no_of_levels'),
				DB::raw('count(approval_type_statuses.id) as no_of_status'),
				DB::raw('IF(approval_types.deleted_at IS NULL,"Active","Inactive") as status')
			)
		/*->where('customers.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->customer_code)) {
					$query->where('customers.code', 'LIKE', '%' . $request->customer_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->customer_name)) {
					$query->where('customers.name', 'LIKE', '%' . $request->customer_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->mobile_no)) {
					$query->where('customers.mobile_no', 'LIKE', '%' . $request->mobile_no . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->email)) {
					$query->where('customers.email', 'LIKE', '%' . $request->email . '%');
				}
			})*/
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
			])
				->first();
			$this->data['action'] = 'View';
		} else {
			return response()->json(['success' => false, 'error' => 'Approval Type ID not found']);
		}
		return response()->json($this->data);
	}
}
