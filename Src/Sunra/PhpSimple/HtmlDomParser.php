<?php
// say me who add "О╩©" into start of file?
// Becouse of this 3 symbol before <?php - php get error
// i think it Sublime Text

namespace Sunra\PhpSimple;

require 'simplehtmldom_1_5'.DIRECTORY_SEPARATOR.'simple_html_dom.php';

class HtmlDomParser {
	
    static public function file_get_html() {
		return call_user_func_array ( '\file_get_html' , func_get_args() );
    }

    // get html dom from string
    static public function str_get_html() {
		return call_user_func_array ( '\str_get_html' , func_get_args() );
	}
}