<?php

namespace Abs\ApprovalPkg;
use Abs\ApprovalPkg\ApprovalLevel;
use App\ActivityLog;
use App\Config;
use App\Http\Controllers\Controller;
use App\Permission;
use App\User;
use App\Role;
use Abs\ApprovalPkg\ApprovalLevelMail;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ApprovalLevelController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getApprovalLevelFilter() {
		$this->data['category_list'] = Collect(Config::getCategoryList()->prepend(['id' => '', 'name' => 'Select Category']));
		return response()->json($this->data);
	}

	public function getApprovalLevelList(Request $request) {
		// dd($request->all());
		$approval_levels = ApprovalLevel::withTrashed()->select(
			'approval_levels.id',
			'approval_levels.name',
			'configs.name as entity',
			'approval_levels.approval_order',
			'cs.name as current_status',
			'ns.name as next_status',
			'rs.name as rejected_status',
			DB::raw('IF(approval_levels.deleted_at IS NULL, "Active","Inactive") as status')
		)
			->leftJoin('configs', 'configs.id', 'approval_levels.category_id')
			->leftJoin('entity_statuses as cs', 'cs.id', 'approval_levels.current_status_id')
			->leftJoin('entity_statuses as ns', 'ns.id', 'approval_levels.next_status_id')
			->leftJoin('entity_statuses as rs', 'rs.id', 'approval_levels.reject_status_id')
			->where(function ($query) use ($request) {
				if (!empty($request->approval_level_name)) {
					$query->where('approval_levels.name', 'LIKE', '%' . $request->approval_level_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->category)) {
					$query->where('approval_levels.category_id', $request->category);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('approval_levels.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('approval_levels.deleted_at');
				}
			})
		;

		return Datatables::of($approval_levels)
			->addColumn('name', function ($approval_level) {
				$status = $approval_level->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $approval_level->name;
			})
			->addColumn('action', function ($approval_level) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-approval-level')) {
					$output .= '<a href="#!/approval-pkg/approval-level/edit/' . $approval_level->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-approval-level')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#approval_level-delete-modal" onclick="angular.element(this).scope().deleteApprovalLevel(' . $approval_level->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getApprovalLevelFormData(Request $request) {
		$id = $request->id;
		$to_emails = $cc_emails = [];
		if (!$id) {
			$approval_level = new ApprovalLevel;
			$action = 'Add';
		} else {
			$approval_level = ApprovalLevel::withTrashed()->find($id);
			$action = 'Edit';
			//Added to cc mail by Karthick T on 10-02-2021
			if(isset($approval_level->mail_type_id) && $approval_level->mail_type_id){
				$to_mail_ids = ApprovalLevelMail::where('approval_level_id',$approval_level->id)
					->pluck('to_entity_id')->toArray();
				$cc_mail_ids = ApprovalLevelMail::where('approval_level_id',$approval_level->id)
					->pluck('cc_entity_id')->toArray();
				if($approval_level->mail_type_id == 11510){ //User
					$to_emails = User::select('id','name')
							->whereIn('id', $to_mail_ids)
							->get();
					$cc_emails = User::select('id','name')
							->whereIn('id', $cc_mail_ids)
							->get();
				}else if($approval_level->mail_type_id == 11511){ //Role
					$to_emails = Role::select('id','name')
							->whereIn('id', $to_mail_ids)
							->get();
					$cc_emails = Role::select('id','name')
							->whereIn('id', $cc_mail_ids)
							->get();
				}
			}
			//Added to cc mail by Karthick T on 10-02-2021
		}
		$this->data['category_list'] = Collect(Config::getCategoryList()->prepend(['id' => '', 'name' => 'Select Category']));
		$this->data['entity_status_list'] = Collect(EntityStatus::query()->company()->get()->prepend(['id' => '', 'name' => 'Select Status']));
		$this->data['approval_level'] = $approval_level;
		//Added to cc mail by Karthick T on 10-02-2021
		$this->data['mail_type_list'] = Collect(Config::select('name', 'id')->where('config_type_id', 200)->get()->prepend(['id' => '', 'name' => 'Select Mail Type']));
		$this->data['to_emails'] = $to_emails;
		$this->data['cc_emails'] = $cc_emails;
		//Added to cc mail by Karthick T on 10-02-2021
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveApprovalLevel(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
				'category_id.required' => 'Category is Required',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:approval_levels,name,' . $request->id . ',id,category_id,' . $request->category_id,
				],
				'category_id' => 'required',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$approval_level = new ApprovalLevel;
				$approval_level->created_by_id = Auth::user()->id;
				$approval_level->created_at = Carbon::now();
				$approval_level->updated_at = NULL;
			} else {
				$approval_level = ApprovalLevel::withTrashed()->find($request->id);
				$approval_level->updated_by_id = Auth::user()->id;
				$approval_level->updated_at = Carbon::now();
			}
			$approval_level->fill($request->all());
			if ($request->status == 'Inactive') {
				$approval_level->deleted_at = Carbon::now();
				$approval_level->deleted_by_id = Auth::user()->id;
			} else {
				$approval_level->deleted_by_id = NULL;
				$approval_level->deleted_at = NULL;
			}
			//Added to cc mail by Karthick T on 10-02-2021
			if(isset($request->mail_type_id) && $request->mail_type_id)
				$approval_level->mail_type_id = $request->mail_type_id;
			$approval_level->save();

			$parent = $request->category_id . '-verification';
			$permissions = [
				[
					'display_order' => 999,
					'parent' => $parent,
					'name' => $approval_level->id . '-verification',
					'display_name' => $approval_level->name,
				],
			];
			Permission::createFromArrays($permissions);

			//Save to cc mail by Karthick T on 10-02-2021
			$to_mails = $cc_mails = [];
			if(isset($request->to_mails) && $request->to_mails){
				$selected_to_mails = json_decode($request->to_mails);
				if ($request->to_mails) {
					if (sizeof($selected_to_mails) > 0) {
						foreach ($selected_to_mails as $key => $value) {
							$to_mails[] = $value->id;
						}
					}
				}
			}
			if(isset($request->cc_mails) && $request->cc_mails){
				$selected_cc_mails = json_decode($request->cc_mails);
				if ($request->cc_mails) {
					if (sizeof($selected_cc_mails) > 0) {
						foreach ($selected_cc_mails as $key => $value) {
							$cc_mails[] = $value->id;
						}
					}
				}
			}
			$remove_approval_level_mail = ApprovalLevelMail::where('approval_level_id',$approval_level->id)
				->forceDelete();
			if(count($to_mails) > 0){
				foreach ($to_mails as $key => $to_mail) {
					$approvel_level_mail = new ApprovalLevelMail;
					$approvel_level_mail->approval_level_id = $approval_level->id;
					$approvel_level_mail->to_entity_id = $to_mail;
					$approvel_level_mail->created_by_id = Auth::user()->id;
					$approvel_level_mail->save();
				}
			}
			if(count($cc_mails) > 0){
				foreach ($cc_mails as $key => $cc_mail) {
					$approvel_level_mail = new ApprovalLevelMail;
					$approvel_level_mail->approval_level_id = $approval_level->id;
					$approvel_level_mail->cc_entity_id = $cc_mail;
					$approvel_level_mail->created_by_id = Auth::user()->id;
					$approvel_level_mail->save();
				}
			}
			//Save to cc mail by Karthick T on 10-02-2021

			$activity = new ActivityLog;
			$activity->date_time = Carbon::now();
			$activity->user_id = Auth::user()->id;
			$activity->module = 'Verification Level';
			$activity->entity_id = $approval_level->id;
			$activity->entity_type_id = 386;
			$activity->activity_id = $request->id == NULL ? 280 : 281;
			$activity->activity = $request->id == NULL ? 280 : 281;
			$activity->details = json_encode($activity);
			$activity->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Verification Level Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Verification Level Updated Successfully',
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

	public function deleteApprovalLevel(Request $request) {
		DB::beginTransaction();
		try {
			$approval_level = ApprovalLevel::withTrashed()->where('id', $request->id)->forceDelete();
			if ($approval_level) {
				$permission = Permission::where('name', $request->id . '-verification')->forceDelete();

				$activity = new ActivityLog;
				$activity->date_time = Carbon::now();
				$activity->user_id = Auth::user()->id;
				$activity->module = 'Verification Level';
				$activity->entity_id = $request->id;
				$activity->entity_type_id = 386;
				$activity->activity_id = 282;
				$activity->activity = 282;
				$activity->details = json_encode($activity);
				$activity->save();

				DB::commit();
				return response()->json(['success' => true, 'message' => 'Verification Level Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	//For get mail list detail by Karthick T on 10-02-2021
	public function getMailData(Request $request){
		$to_mail_list = $cc_mail_list = [];
		if(isset($request->mail_type_id) && $request->mail_type_id){
			if($request->mail_type_id == 11510){ //User
				$to_mail_list = $cc_mail_list = User::select('id','name')
						->where('company_id', Auth::user()->company_id)
						->where('user_type_id',1) //Employee
						->get();
			}else if($request->mail_type_id == 11511){ //Role
				$to_mail_list = $cc_mail_list = Role::select('id','name')
						->get();
			}
		}
		$this->data['to_mail_list'] = $to_mail_list;;
		$this->data['cc_mail_list'] = $cc_mail_list;
		return response()->json($this->data);
	}
	//For get mail list detail by Karthick T on 10-02-2021

}