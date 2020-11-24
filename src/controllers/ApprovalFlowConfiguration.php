<?php

namespace Abs\ApprovalPkg;
use Abs\ApprovalPkg\ApprovalFlowConfiguration;
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

class ApprovalFlowConfigurationController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getApprovalFlowConfigurationFilter() {
		$this->data['category_list'] = Collect(Config::getCategoryList()->prepend(['id' => '', 'name' => 'Select Category']));
		return response()->json($this->data);
	}

	public function getApprovalFlowConfigurationList(Request $request) {
		// dd($request->all());
		$approval_flow_configurations = ApprovalFlowConfiguration::withTrashed()->select(
			'approval_flow_configurations.id',
			'approval_flow_configurations.value',
			'configs.name as approval_type',
			'approval_levels.name as approval_level',
			'ns.name as next_status',
			DB::raw('IF(approval_flow_configurations.deleted_at IS NULL, "Active","Inactive") as status')
		)
			->leftJoin('approval_levels', 'approval_levels.id', 'approval_flow_configurations.approval_level_id')
			->leftJoin('configs', 'configs.id', 'approval_levels.category_id')
			->leftJoin('entity_statuses as ns', 'ns.id', 'approval_flow_configurations.next_status_id')
		// ->where(function ($query) use ($request) {
		// 	if (!empty($request->entity_status_name)) {
		// 		$query->where('approval_flow_configurations.name', 'LIKE', '%' . $request->entity_status_name . '%');
		// 	}
		// })
		// ->where(function ($query) use ($request) {
		// 	if (!empty($request->entity_id)) {
		// 		$query->where('approval_flow_configurations.entity_id', $request->entity_id);
		// 	}
		// })
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('approval_flow_configurations.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('approval_flow_configurations.deleted_at');
				}
			})
		;

		return Datatables::of($approval_flow_configurations)
			->addColumn('name', function ($approval_flow_configuration) {
				$status = $approval_flow_configuration->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $approval_flow_configuration->name;
			})
			->addColumn('action', function ($approval_flow_configuration) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-entity-status')) {
					$output .= '<a href="#!/approval-pkg/entity-status/edit/' . $approval_flow_configuration->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-entity-status')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#approval_flow_configuration-delete-modal" onclick="angular.element(this).scope().deleteApprovalFlowConfiguration(' . $approval_flow_configuration->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getApprovalFlowConfigurationFormData(Request $request) {
		// dd($request->all());
		$id = $request->id;
		if (!$id) {
			$approval_flow_configuration = new ApprovalFlowConfiguration;
			$action = 'Add';
		} else {
			$approval_flow_configuration = ApprovalFlowConfiguration::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['extras'] = [
			'approval_level_list' => Collect(ApprovalLevel::select('name', 'id')->get()->prepend(['id' => '', 'name' => 'Select Approval Level'])),
			'entity_status_list' => Collect(EntityStatus::query()->company()->get()->prepend(['id' => '', 'name' => 'Select Status'])),
		];
		$this->data['approval_flow_configuration'] = $approval_flow_configuration;
		$this->data['action'] = $action;
		$this->data['theme'];
		// dd($this->data);
		return response()->json($this->data);
	}

	public function saveApprovalFlowConfiguration(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'approval_level_id.required' => 'Name is Required',
				'value.required' => 'Name is Required',
				'next_status_id.required' => 'Name is Required',
			];
			$validator = Validator::make($request->all(), [
				'approval_level_id' => 'required',
				'value' => 'required',
				'next_status_id' => 'required',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$approval_flow_configuration = new ApprovalFlowConfiguration;
				$approval_flow_configuration->created_by_id = Auth::user()->id;
				$approval_flow_configuration->created_at = Carbon::now();
				$approval_flow_configuration->updated_at = NULL;
			} else {
				$approval_flow_configuration = ApprovalFlowConfiguration::withTrashed()->find($request->id);
				$approval_flow_configuration->updated_by_id = Auth::user()->id;
				$approval_flow_configuration->updated_at = Carbon::now();
			}
			$approval_flow_configuration->fill($request->all());
			if ($request->status == 'Inactive') {
				$approval_flow_configuration->deleted_at = Carbon::now();
				$approval_flow_configuration->deleted_by_id = Auth::user()->id;
			} else {
				$approval_flow_configuration->deleted_by_id = NULL;
				$approval_flow_configuration->deleted_at = NULL;
			}
			$approval_flow_configuration->company_id = Auth::user()->company_id;

			$approval_flow_configuration->save();

			$activity = new ActivityLog;
			$activity->date_time = Carbon::now();
			$activity->user_id = Auth::user()->id;
			$activity->module = 'Approval Flow Configuration';
			$activity->entity_id = $approval_flow_configuration->id;
			$activity->entity_type_id = 388;
			$activity->activity_id = $request->id == NULL ? 280 : 281;
			$activity->activity = $request->id == NULL ? 280 : 281;
			$activity->details = json_encode($activity);
			$activity->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Approval Flow Configuration Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Approval Flow Configuration Updated Successfully',
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

	public function deleteApprovalFlowConfiguration(Request $request) {
		DB::beginTransaction();
		try {
			$approval_flow_configuration = ApprovalFlowConfiguration::withTrashed()->where('id', $request->id)->forceDelete();
			if ($approval_flow_configuration) {

				$activity = new ActivityLog;
				$activity->date_time = Carbon::now();
				$activity->user_id = Auth::user()->id;
				$activity->module = 'Approval Flow Configuration';
				$activity->entity_id = $request->id;
				$activity->entity_type_id = 388;
				$activity->activity_id = 282;
				$activity->activity = 282;
				$activity->details = json_encode($activity);
				$activity->save();

				DB::commit();
				return response()->json(['success' => true, 'message' => 'Approval Flow Configuration Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

}