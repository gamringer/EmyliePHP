<?php

namespace Emylie\Util {

	function cast($instance, $className) {
	    return unserialize(sprintf(
	        'O:%d:"%s"%s',
	        strlen($className),
	        $className,
	        strstr(strstr(serialize($instance), '"'), ':')
	    ));
	}

}