<?php

namespace Emylie\ACL {

	use \Emylie\Core\Config;
	use \Emylie\ACL\Models\Role;
	use \Emylie\ACL\Models\Permission;

	Trait ACAuthenticatable{

		/**
		 * Holds all the permission codes's that this member has access to
		 */
		private $_permissionCodes = array();

		/**
		 * This method is called to check if a user has access to a certain
		 * custom defined piece of code.
		 *
		 * @var $code is the code we use to lookup this acl permission
		 */
		public function hasAccess($code) {

			// first we need to populate the list of permission_code's
			// this user so we can store in memory for the
			// remaining of this scripts execution.
			$this->_setPermissionCodesArray();

			// now we have to see if this member has access
			// to the section defined by the given code.
			return in_array($code, $this->_permissionCodes);

		}

		/**
		 * This method will get all the role objects in the database for this ACAuthenticatable type
		 */
		public static function getRoles($options = array()) {
			$oldTableName = Role::$table_name;
			Role::$table_name = Config::$config['ACL'][static::$table_name]['table_prefix'] . Role::$table_name;

			// get all permission objects
			$roles = Role::findAll($options);

			foreach ($roles as $role) {
				$role->ac_authenticatable_group = Config::$config['ACL'][static::$table_name]['table_prefix'];
			}

			Role::$table_name = $oldTableName;

			return $roles;
		}

		public static function getOneRole($options) {
			$options['limit'] = 1;
			$result = static::getRoles($options);

			return isset($result[0]) ? $result[0] : null;
		}

		/**
		 * This method will get all the permission objects in the database for this ACAuthenticatable type
		 */
		public static function getPermissions($options = array()) {
			$oldTableName = Permission::$table_name;

			Permission::$table_name = Config::$config['ACL'][static::$table_name]['table_prefix'] . Permission::$table_name;

			// get all permission objects
			$permissions = Permission::findAll($options);

			Permission::$table_name = $oldTableName;

			return $permissions;
		}

		/**
		 * This method will return the role this ACAthenticatable user belongs to
		 */
		public function getRole() {
			$oldTableName = Role::$table_name;
			Role::$table_name = Config::$config['ACL'][static::$table_name]['table_prefix'] . Role::$table_name;

			$role = Role::find($this->info['role_id']);
			$role->ac_authenticatable_group = Config::$config['ACL'][static::$table_name]['table_prefix'];

			Role::$table_name = $oldTableName;

			return $role;
		}

		/**
		 * This method will return the permission objects (by role and custom)
		 * that this this ACAthenticatable user has
		 */
		public function getAllPermissions($options = array()) {
			// get the role permissions
			$permissions = $this->getRole()->getPermissions($options);

			$rolePermissionIds = [];
			if (is_array($permissions)) {
				foreach ($permissions as $p) {
					$rolePermissionIds[] = $p->ID;
				}
			}

			// get custom permissions
			$customPermissions = $this->getCustomPermissions($options);

			// combine
			foreach ($customPermissions as $cp) {
				if (!in_array($cp->ID, $rolePermissionIds)) {
					$permissions[] = $cp;
				}
			}

			return $permissions;
		}

		public static function getRoleIdByCode($code) {
			$oldTableName = Role::$table_name;
			Role::$table_name = Config::$config['ACL'][static::$table_name]['table_prefix'] . Role::$table_name;

			$role = Role::findOne([
				'where' => [
					// make sure owner role is in DB
					'code = \''.$code.'\''
				]
			]);

			Role::$table_name = $oldTableName;

			return (is_numeric($role->ID)) ? $role->ID: null;
		}

		/**
		 * This method will return the custom permission objects
		 * that this this ACAthenticatable user has
		 */
		public function getCustomPermissions($options = array()) {
			$options['joins'] = [
				Config::$config['ACL'][static::$table_name]['table_prefix'] . 'custom_permissions' => Permission::$id_field
			];
			$options['where'] = [
				Config::$config['ACL'][static::$table_name]['table_prefix'] . 'custom_permissions.' . Config::$config['ACL'][static::$table_name]['id_name'].' = '.$this->ID
			];

			$oldTableName = Permission::$table_name;
			Permission::$table_name = Config::$config['ACL'][static::$table_name]['table_prefix'] . Permission::$table_name;

			$customPermissions = Permission::findAll($options);

			Permission::$table_name = $oldTableName;

			return $customPermissions;
		}

		/**
		 * This method will set the permission code array. It is used
		 * in the hasAccess method.
		 */
		private function _setPermissionCodesArray($overrideCache = false) {

			// maybe the permission ids are already loaded in memory
    		if (count($this->_permissionCodes) == 0) {

    			// no permissions set yet

				// get permission_ids from the role this user belongs to
				$allPermissions = $this->getAllPermissions();

				foreach ($allPermissions as $permission) {
					$this->_permissionCodes[] = $permission->info['code'];
				}
    		}

		}

	}

}