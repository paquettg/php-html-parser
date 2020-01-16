<?php declare(strict_types=1);
namespace PHPHtmlParser;

use PHPHtmlParser\Exceptions\CurlException;

/**
 * Class Curl
 *
 * @package PHPHtmlParser
 */
class Curl implements CurlInterface
{

    /**
     * A simple curl implementation to get the content of the url.
     *
     * @param string $url
     * @param array $options
     * @return string
     * @throws CurlException
     */
    public function get(string $url, array $options): string
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new CurlException('Curl Init return `false`.');
        }

        if ( ! ini_get('open_basedir')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        if (isset($options['curlHeaders'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $options['curlHeaders']);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
        curl_setopt($ch, CURLOPT_URL, $url);

        $content = curl_exec($ch);
        if ($content === false) {
            // there was a problem
            $error = curl_error($ch);
            throw new CurlException('Error retrieving "'.$url.'" ('.$error.')');
        } elseif ($content === true) {
            throw new CurlException('Unexpected return value of content set to true.');
        }

        return $content;
    }
}
