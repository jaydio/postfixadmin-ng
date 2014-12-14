<?php


function edit_active($p,$r){
	//Pull in a few variables from PFA
	global $CONF;
	global $PALANG;

	// Set defaults
	$p['table'] = isset($p['table']) ? $p['table'] : '';
	$p['id'] = isset($p['id']) ? $p['id'] : '';
	$p['active'] = isset($p['active']) ? $p['active'] : '';

	// Define requirements
	$required = array();
	$required['table'] = array('type'=>'table','desc'=>'table to modify');
	$required['id'] = array('type'=>'str','desc'=>'id to modify');
	$required['active'] = array('type'=>'bool','desc'=>'active');

	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		$admin_username = authentication_get_username();
		$handlerclass = ucfirst($p['table']) . 'Handler';
		if (preg_match('/^[a-z]+$/', $p['table']) && file_exists("../model/$handlerclass.php")){
			$handler = new $handlerclass(0, $admin_username);
			$formconf = $handler->webformConfig();
			authentication_require_role($formconf['required_role']);
			if($handler->init($p['id'])){
				if(in_array($p['active'],array(0,1))){
					if($handler->set(array('active' => $p['active']))){
						$handler->store();
						$r['code'] = 0;
						$r['status'] = 'ok';
					}else{
						$r['code'] = 1;
						$r['errors'][] = 'Error setting value';
					}
				}else{
					$r['code'] = 2;
					$r['errors'][] = 'Invalid value';
				}
			}else{
				$r['code'] = 3;
				$r['errors'][] = $handler->errormsg;
				$r['info'] = $handler->infomsg;
			}
		}else{
			$r['code'] = 4;
			$r['errors'][] = 'Invalid table';
		}
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);

}
?>