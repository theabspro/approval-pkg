<?php
namespace Abs\ApprovalPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class ApprovalPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//MASTER > CUSTOMERS
			4800 => [
				'display_order' => 10,
				'parent_id' => null,
				'name' => 'approval-levels',
				'display_name' => 'Approval Levels',
			],
			4801 => [
				'display_order' => 1,
				'parent_id' => 4800,
				'name' => 'add-approval-level',
				'display_name' => 'Add',
			],
			4802 => [
				'display_order' => 2,
				'parent_id' => 4800,
				'name' => 'edit-approval-level',
				'display_name' => 'Edit',
			],
			4803 => [
				'display_order' => 3,
				'parent_id' => 4800,
				'name' => 'delete-approval-level',
				'display_name' => 'Delete',
			],

		];

		foreach ($permissions as $permission_id => $permsion) {
			$permission = Permission::firstOrNew([
				'id' => $permission_id,
			]);
			$permission->fill($permsion);
			$permission->save();
		}
	}
}