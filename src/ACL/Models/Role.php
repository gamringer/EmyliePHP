<?php

namespace Emylie\ACL\Models {

	use \Emylie\Core\Data\Model;
	use \Emylie\ACL\Models\Permission;

	class Role extends Model{

		public $ac_authenticatable_group;

		protected static $_instances = array();

		public static $i_name = 'main';
		public static $fields = [
			'role_id', 'name', 'code', 'description'
		];
		public static $table_name = 'roles';
		public static $id_field = 'role_id';

		public function getPermissions($options = array()) {

			$options['joins'] = [
				$this->ac_authenticatable_group . 'role_permissions' => Permission::$id_field
			];
			$options['where'] = [
				$this->ac_authenticatable_group . 'role_permissions.' . static::$id_field .' = '.$this->info['role_id']
			];

			$oldTableName = Permission::$table_name;
			Permission::$table_name = $this->ac_authenticatable_group . Permission::$table_name;

			$rolePermissions = Permission::findAll($options);

			Permission::$table_name = $oldTableName;

			return $rolePermissions;


		}

	}
}
