<?php
/*
Controller name: Header
Controller description: Query the recent list of the custom menu items.
*/
class JSON_API_Header_Controller {

  public function get_header() {

  	global $json_api;
  	$name = $json_api->query->name;

    return array(   "header" => 'poop'	);
  }

}

?>