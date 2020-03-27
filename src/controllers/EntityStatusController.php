<?php

namespace Abs\ApprovalPkg;
use Abs\ApprovalPkg\EntityStatus;
use App\ActivityLog;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class EntityStatusController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getEntityStatusFilter() {
		$this->data['category_list'] = Collect(Config::getCategoryList()->prepend(['id' => '', 'name' => 'Select Category']));
		return response()->json($this->data);
	}

	public function getEntityStatusList(Request $request) {
		$entity_statuses = EntityStatus::withTrashed()->select(
			'entity_statuses.id',
			'entity_statuses.name',
			'configs.name as entity',
			DB::raw('IF(entity_statuses.deleted_at IS NULL, "Active","Inactive") as status')
		)
			->leftJoin('configs', 'configs.id', 'entity_statuses.entity_id')
			->where(function ($query) use ($request) {
				if (!empty($request->entity_status_name)) {
					$query->where('entity_statuses.name', 'LIKE', '%' . $request->entity_status_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->entity_id)) {
					$query->where('entity_statuses.entity_id', $request->entity_id);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('entity_statuses.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('entity_statuses.deleted_at');
				}
			})
		;

		return Datatables::of($entity_statuses)
			->addColumn('name', function ($entity_status) {
				$status = $entity_status->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $entity_status->name;
			})
			->addColumn('action', function ($entity_status) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-approval-level')) {
					$output .= '<a href="#!/approval-pkg/approval-level/edit/' . $entity_status->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-approval-level')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#entity_status-delete-modal" onclick="angular.element(this).scope().deleteEntityStatus(' . $entity_status->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getEntityStatusFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$entity_status = new EntityStatus;
			$action = 'Add';
		} else {
			$entity_status = EntityStatus::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['entity_list'] = Collect(Config::getCategoryList()->prepend(['id' => '', 'name' => 'Select Entity']));
		$this->data['entity_status'] = $entity_status;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveEntityStatus(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
				'entity_id.required' => 'Category is Required',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:entity_statuses,name,' . $request->id . ',id,entity_id,' . $request->entity_id . ',company_id,' . Auth::user()->company_id,
				],
				'entity_id' => 'required',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$entity_status = new EntityStatus;
				$entity_status->created_by_id = Auth::user()->id;
				$entity_status->created_at = Carbon::now();
				$entity_status->updated_at = NULL;
			} else {
				$entity_status = EntityStatus::withTrashed()->find($request->id);
				$entity_status->updated_by_id = Auth::user()->id;
				$entity_status->updated_at = Carbon::now();
			}
			$entity_status->fill($request->all());
			if ($request->status == 'Inactive') {
				$entity_status->deleted_at = Carbon::now();
				$entity_status->deleted_by_id = Auth::user()->id;
			} else {
				$entity_status->deleted_by_id = NULL;
				$entity_status->deleted_at = NULL;
			}
			$entity_status->company_id = Auth::user()->company_id;

			$entity_status->save();

			$activity = new ActivityLog;
			$activity->date_time = Carbon::now();
			$activity->user_id = Auth::user()->id;
			$activity->module = 'Entity Status';
			$activity->entity_id = $entity_status->id;
			$activity->entity_type_id = 1420;
			$activity->activity_id = $request->id == NULL ? 280 : 281;
			$activity->activity = $request->id == NULL ? 280 : 281;
			$activity->details = json_encode($activity);
			$activity->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Approval Level Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Approval Level Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteEntityStatus(Request $request) {
		DB::beginTransaction();
		try {
			$entity_status = EntityStatus::withTrashed()->where('id', $request->id)->forceDelete();
			if ($entity_status) {

				$activity = new ActivityLog;
				$activity->date_time = Carbon::now();
				$activity->user_id = Auth::user()->id;
				$activity->module = 'Approval Level';
				$activity->entity_id = $request->id;
				$activity->entity_type_id = 1420;
				$activity->activity_id = 282;
				$activity->activity = 282;
				$activity->details = json_encode($activity);
				$activity->save();

				DB::commit();
				return response()->json(['success' => true, 'message' => 'Approvel Level Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

}