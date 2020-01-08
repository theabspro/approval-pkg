<?php
namespace Abs\ApprovalPkg\Database\Seeds;

use Abs\ApprovalPkg\ApprovalLevel;
use Abs\ApprovalPkg\ApprovalType;
use Abs\ApprovalPkg\ApprovalTypeStatus;
use App\Permission;
use Illuminate\Database\Seeder;

class ApprovalPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {

		$approval_types = [
			1 => [
				'name' => 'CN DN Approvals',
				'code' => 'cn-dn-approvals',
				'filter_field' => 'status_id',
			],
		];

		$approval_type_statuses = [
			1 => [
				'approval_type_id' => 1,
				'status' => 'New',
			],
			2 => [
				'approval_type_id' => 1,
				'status' => 'Approval 1 Pending',
			],
			3 => [
				'approval_type_id' => 1,
				'status' => 'Approval 2 Pending',
			],
			4 => [
				'approval_type_id' => 1,
				'status' => 'Approved',
			],
			5 => [
				'approval_type_id' => 1,
				'status' => 'Approval 1 Rejected',
			],
			6 => [
				'approval_type_id' => 1,
				'status' => 'Approval 2 Rejected',
			],
		];

		$approval_levels = [
			1 => [
				'approval_type_id' => 1,
				'name' => 'CN/DN Approval 1',
				'approval_order' => 1,
				'current_status_id' => 2,
				'next_status_id' => 4,
				'reject_status_id' => 5,
			],
		];
		foreach ($approval_types as $id => $data) {
			$record = ApprovalType::firstOrNew([
				'id' => $id,
			]);
			$record->fill($data);
			$record->save();

			$permissions = [
				[
					'display_order' => 99,
					'parent' => null,
					'name' => $data['code'],
					'display_name' => $data['name'],
				],
			];
			Permission::createFromArrays($permissions);

		}
		foreach ($approval_type_statuses as $id => $data) {
			$record = ApprovalTypeStatus::firstOrNew([
				'id' => $id,
			]);
			$record->fill($data);
			$record->save();
		}
		foreach ($approval_levels as $id => $data) {
			$record = ApprovalLevel::firstOrNew([
				'id' => $id,
			]);
			$record->fill($data);
			$record->save();

			$approval_type = ApprovalType::find($data['approval_type_id']);
			$permissions = [
				[
					'display_order' => 99,
					'parent' => $approval_type->code,
					'name' => $data['name'],
					'display_name' => $data['name'],
				],
				[
					'display_order' => 1,
					'parent' => $data['name'],
					'name' => $data['name'] . ' View All',
					'display_name' => $data['name'] . ' View All',
				],
				[
					'display_order' => 2,
					'parent' => $data['name'],
					'name' => $data['name'] . ' Outlet Based',
					'display_name' => $data['name'] . ' Outlet Based',
				],
				[
					'display_order' => 3,
					'parent' => $data['name'],
					'name' => $data['name'] . ' Sub Employee Based',
					'display_name' => $data['name'] . ' Sub Employee Based',
				],
			];
			Permission::createFromArrays($permissions);

		}
	}
}
