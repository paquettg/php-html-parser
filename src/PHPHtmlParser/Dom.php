<?php
namespace PHPHtmlParser;

use Dom\HtmlNode;

class Parser {
	
	/**
	 * Contains the root node of this dom tree.
	 *
	 * @var HtmlNode
	 */
	protected $root;

	/**
	 * The document string.
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * The original file size of the document.
	 *
	 * @var int
	 */
	protected $originalSize;
	
	/**
	 * The size of the document after it is cleaned.
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * Attempts to load the dom from a string.
	 *
	 * @param string $str
	 * @chainable
	 */
	public function load($str)
	{
		$this->originalSize = strlen($str);

		// clean out none-html text
		$html = $this->clean($str);

		$this->size    = strlen($str);
		$this->content = $html;
	}

	/**
	 * Loads the dom from a document file/url
	 *
	 * @param string $file
	 * @chainable
	 */
	public function loadFromFile($file)
	{
		$fp = fopen($file, 'r');
		$document = fread($fp, filesize($file));
		fclose($fp);

		return $this->load($file);
	}

	/**
	 * Cleans the html of any none-html information.
	 *
	 * @param string $str
	 * @return string
	 */
	protected function clean($str)
	{
		// clean out the \n\r
		$str = str_replace(["\r\n", "\r", "\n"], ' ', $str);

        // strip out comments
        $str = preg_replace("'<!--(.*?)-->'is", '', $str);
        
        // strip out cdata
        $str = preg_replace("'<!\[CDATA\[(.*?)\]\]>'is", '', $str);
        
        // strip out <script> tags
        $str = preg_replace("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is", '', $str);
        $str = preg_replace("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is", '', $str);
        
        // strip out <style> tags
        $str = preg_replace("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is", '', $str);
        $str = preg_replace("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is", '', $str);
        
        // strip out preformatted tags
        $str = preg_replace("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is", '', $str);
        
        // strip out server side scripts
        $str = preg_replace("'(<\?)(.*?)(\?>)'s", '', $str);
        
        // strip smarty scripts
        $str = preg_replace("'(\{\w)(.*?)(\})'s", '', $str);

		return $str;
	}
}
