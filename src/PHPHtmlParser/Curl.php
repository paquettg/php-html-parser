<?php
namespace PHPHtmlParser;

use PHPHtmlParser\Exceptions\CurlException;

class Curl implements CurlInterface {
	
	/**
	 * A simple curl implementation to get the content of the url.
	 *
	 * @param string $url
	 * @return string
	 * @throws CurlException
	 */
	public function get($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

		$content = curl_exec($ch);
		if ($content === false)
		{
			// there was a problem
			$error = curl_error($ch);
			throw new CurlException('Error retrieving "'.$url.'" ('.$error.')');
		}

		return $content;
	}
}
