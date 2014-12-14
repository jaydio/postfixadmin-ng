<?php

function validate_domain($domain){
	return(filter_var(gethostbyname($domain), FILTER_VALIDATE_IP));
}
function validate_domain_nolookup($domain){
	$parsed = parse_url($domain);
	$host = $parsed['path']; //should be just the domain
	return($host==$domain && filter_var('http://'.$domain, FILTER_VALIDATE_URL ));
}
function validate_email($email){
	return(filter_var($email, FILTER_VALIDATE_EMAIL));
}
function validate_bool($bool){
	return(filter_var($bool, FILTER_VALIDATE_BOOLEAN) && ($bool===true||$bool===false));
}
function validate_int($int){
	$int = (int)$int;
	return(is_int($int));
}
function validate_str($str){
	$str = trim($str);
	return(!empty($str));
}

function validate_fields($required,$p,$r){
	$invalid = array();
	if(is_array($required)){
		foreach($required as $key => $req){
			if(isset($p[$key])){
				switch($req['type']){
					case 'int':
						$invalid[$key] = validate_int($p[$key]) ? false : $req['desc'].' failed validation';
					break;
					case 'bool':
						$invalid[$key] = validate_bool($p[$key]) ? false : $req['desc'].' failed validation';
					break;
					case 'str':
						$invalid[$key] = validate_str($p[$key]) ? false : $req['desc'].' failed validation';
					break;
					case 'domain':
						$invalid[$key] = validate_domain($p[$key]) ? false : $req['desc'].' failed validation';
					break;
					case 'domain_nolookup':
						$invalid[$key] = validate_domain_nolookup($p[$key]) ? false : $req['desc'].' failed validation';
					break;
					case 'email':
						$invalid[$key] = validate_email($p[$key]) ? false : $req['desc'].' failed validation';
					break;
					case 'email_slug':
						$invalid[$key] = validate_email($p[$key].'@domain.com') ? false : $req['desc'].' failed validation';
					break;
					case 'table':
							$invalid[$key] = in_array($p[$key],array('alias','mailbox','domain')) ? false : $req['desc'].' failed validation';
					break;
					case 'email_list':
						$list = array_map('trim',explode("\n",$p[$key]));
						foreach($list as $i => $email){
							$invalid[$key.'-'.$i] = validate_email($email) ? false : $req['desc'].' failed validation';
						}
					break;
					default;
						$invalid[$key] = 'Unknown validation key: '.$req['validate'];
					break;
				}
			}else{
				$invalid[$key] = 'Field missing: '.$req['desc'];
			}
		}
		$invalid = array_filter($invalid);
		if(count($invalid)>0){
			$r['errors'] = $invalid;
		}
	}else{
		$r['errors'][] = 'Validation array missing';
	}
	
	return($r);
}


?>