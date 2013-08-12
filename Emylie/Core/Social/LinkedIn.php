<?php

namespace Emylie\Core\Social {

	use \Emylie\Core\HTTPRequest;

	class LinkedIn {

		public static function getUrlShareCount($url) {

			$linkedinShareCountUrl = "http://www.linkedin.com/countserv/count/share?url=".
				$url.
				"&format=json";
			$http = new HTTPRequest($linkedinShareCountUrl);
			$res = json_decode($http->requestResult(), true);

			//var_dump($res);

			return (isset($res['count']) && $res['count']) ? $res['count'] : 0;

		}

	}
}