<?php

namespace Emylie\Core\Social {

	class FB {

		public static function getUrlShareCount($url) {

			$facebook = file_get_contents('http://api.facebook.com/restserver.php?method=links.getStats&urls='.$url);
			$fbbegin = '<share_count>'; $fbend = '</share_count>';
			$fbpage = $facebook;
			$fbparts = explode($fbbegin,$fbpage);
			$fbpage = $fbparts[1];
			$fbparts = explode($fbend,$fbpage);
			$fbcount = $fbparts[0];
			return ($fbparts[0] == '') ? 0 : $fbparts[0];

		}

		public static function getShareUrl($url, $title, $summary, $imageUrl) {

			$title=urlencode($title);
			$url=urlencode($url);
			$summary=urlencode(htmlentities($summary));
			$imageUrl=urlencode($imageUrl);

			return 'http://www.facebook.com/sharer.php?s=100&amp;p[title]='.$title.
				'&amp;p[summary]='.$summary.
				'&amp;p[url]='.$url.
				'&amp;&p[images][0]='.$imageUrl;
		}

	}
}