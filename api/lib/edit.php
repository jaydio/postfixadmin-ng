<?php


function perform_edit($p,$r){
	global $CONF;
	global $PALANG;

	$p['edit'] = isset($p['edit']) ? $p['edit'] : '';
	$p['table'] = isset($p['table']) ? $p['table'] : '';
	$p['values'] = isset($p['values']) ? $p['values'] : array();

	$admin_username = authentication_get_username(); # enforce login
	
	$handlerclass = ucfirst($p['table']) . 'Handler';

	if (preg_match('/^[a-z]+$/', $p['table']) && file_exists("../model/$handlerclass.php")){
		$error = 0;

		$new = (empty($p['edit']) ? 1 : 0);
		$handler = new $handlerclass($new, $admin_username);
		$formconf = $handler->webformConfig();
		authentication_require_role($formconf['required_role']);

		if ($new == 0 || $formconf['early_init']) {
			if (!$handler->init($p['edit'])) {
				$r['errors'] = array_values($handler->errormsg);
				$r['code'] = 5;
			}
		}
		
		if(!isset($r['errors'])){
			$form_fields = $handler->getStruct();
			$id_field	 = $handler->getId_field();
			foreach($form_fields as $key => $field) {
				if ($field['editable'] && $field['display_in_form']) {
					if (!isset($p['values'][$key])) {
						if($field['type'] == 'bool') {
							$values[$key] = 0; # isset() for unchecked checkboxes is always false
						}
					}elseif($field['type'] == 'txtl') {
						$values[$key] = $p['values'][$key];
						$values[$key] = preg_replace ('/\\\r\\\n/', ',', $values[$key]);
						$values[$key] = preg_replace ('/\r\n/',	  ',', $values[$key]);
						$values[$key] = preg_replace ('/,[\s]+/i',  ',', $values[$key]); 
						$values[$key] = preg_replace ('/[\s]+,/i',  ',', $values[$key]); 
						$values[$key] = preg_replace ('/,,*/',		',', $values[$key]);
						$values[$key] = preg_replace ('/,*$|^,*/',  '',  $values[$key]);
						if ($values[$key] == '') {
							$values[$key] = array();
						} else {
							$values[$key] = explode(",", $values[$key]);
						}
					} else {
						$values[$key] = $p['values'][$key];
					}
				}
			}

			if (isset($formconf['hardcoded_edit']) && $formconf['hardcoded_edit']) {
				$values[$id_field] = $form_fields[$id_field]['default'];
			} elseif ($new == 0) {
				$values[$id_field] = $p['edit'];
			}

			if ($new && ($form_fields[$id_field]['display_in_form'] == 0)) {
				if ($form_fields[$id_field]['editable'] == 1) { # address split to localpart and domain?
					$values[$id_field] = $handler->mergeId($values);
				} else { # probably auto_increment
					$values[$id_field] = '';
				}
			}

			if (!$handler->init($values[$id_field])) {
				$r['errors'] = array_values($handler->errormsg);
				$r['code'] = 4;
				$r['info'] = 'Initialization error';
			}else{
				if (!$handler->set($values)) {
					$r['errors'] = array_values($handler->errormsg);
					$r['code'] = 3;
					$r['info'] = 'Validation error';
				}

				$form_fields = $handler->getStruct(); # refresh $form_fields - set() might have changed something

				if (!isset($r['errors'])) {
					if (!$handler->store()) {
						$errormsg = $handler->errormsg;
						$r['errors'] = array_values($handler->errormsg);
						$r['code'] = 2;
						$r['info'] = 'Save error';
					} else {
						$r['info'] = $handler->infomsg;
						if (count($handler->errormsg)) { # might happen if domain_postcreation fails
							$r['errors'][] = array_values($handler->errormsg);
							$r['code'] = 1;
							$r['info'] = 'Unspecified error';
						}else{
							if(isset($r['info']['success'])){
								$r['response'] = $r['info']['success'];
							}else{
								$r['response'] = $r['info'];
							}
							$r['status'] = 'ok';
							$r['code'] = 0;
						}
					}
				}
			}
		}
	}else{
		$r['code'] = 4;
		$r['errors'][] = 'Invalid table';
		$r['info'] = 'Configuration error';
	}
	return($r);
}
?>