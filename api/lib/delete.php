<?php

function perform_delete($p,$r){
	global $CONF;
	global $PALANG;

	$username = authentication_get_username(); # enforce login

	$id	 = $p['delete'];
	$table = $p['table'];

	$handlerclass = ucfirst($table) . 'Handler';

	if (preg_match('/^[a-z]+$/', $table) && file_exists("../model/$handlerclass.php")) {
		$handler = new $handlerclass(0, $username);
		$formconf = $handler->webformConfig();
		authentication_require_role($formconf['required_role']);
		if ($handler->init($id)){
			$handler->delete();
			$r['status'] = 'ok';
			$r['code'] = 0;
		}else{
			$r['code'] = 1;
			$r['errors'] = array_values($handler->errormsg);
		}
	}else{
		$r['code'] = 2;
		$r['errors'][] = 'Invalid table';
		$r['info'] = 'Configuration error';
	}
	return($r);
}

?>