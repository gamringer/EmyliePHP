<?php

namespace Emylie\ACL\Models {

	use \Emylie\Core\Data\Model;

	class Permission extends Model{

		protected static $_instances = array();

		public static $i_name = 'main';
		public static $fields = [
			'permission_id', 'name', 'code', 'description'
		];
		public static $table_name = 'permissions';
		public static $id_field = 'permission_id';

	}
}
