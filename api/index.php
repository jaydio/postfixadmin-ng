<?php
require('../common.php');
require('./lib/domain.php');
require('./lib/alias.php');
require('./lib/aliasdomain.php');
require('./lib/mailbox.php');
require('./lib/validation.php');
require('./lib/encrypt.php');
require('./lib/active.php');
require('./lib/edit.php');
require('./lib/delete.php');
require('./lib/send.php');
require('./lib/list.php');



define('DEFAULT_OUTPUT_FORMAT','json_pretty');					//pre|json|json_pretty
define('ADMIN_TOKEN_FILE','./lib/token/.latocfile');			//protect directory
define('ADMIN_TOKEN_EXPIRE',180);								//seconds to expire token
define('ADMIN_TOKEN_ENTROPY','~$\:hkAar?3,HG5v:,$&X~PSzC._CHGD$2>Dvdvq=g2{4@HF\'F%3Y)M)<00-#Waf{?1oclU*>$]y>W:meNp^32ciD2B785<;y&\'<X9Kt2z3ny@iu{5H(Y2,K]Modl+on~0tGoUE`m8tc[?nG8gw{9>l9J_qS8\Yg%I#>4\z~?pt.Ly;S9CUl[8ses@uR');	//Entropy for encryption (MCRYPT_BlOWFISH and MCRYPT_MODE_CBC)
define('REQUEST_METHOD','GET');									//GET|POST

////End configuration

 //grant required privileges 
$_SESSION['sessid']['roles'] = array('user','admin','global-admin');

//Delete session file after ADMIN_TOKEN_EXPIRE (3 mins)
if(is_file(ADMIN_TOKEN_FILE)) if(time()-filemtime(ADMIN_TOKEN_FILE) >= ADMIN_TOKEN_EXPIRE) unlink(ADMIN_TOKEN_FILE); touch(ADMIN_TOKEN_FILE);

define('OUTPUT_FORMAT',(isset($_GET['output']) ? (in_array($_GET['output'],array('pre','json','json_pretty')) ? $_GET['output'] : DEFAULT_OUTPUT_FORMAT) : DEFAULT_OUTPUT_FORMAT));



//Can login with user and pass each time but for secondary calls (delete) a token must be used.
//Token is output on login calls only
//Token expires after 3 minutes, bugger
//May not even need this token thing, it seems all API calls can be done in a single request

function handle_login($p,$r){
	$_SESSION['sessid'] = array();	//stupid script uses sessions so to make it stand alone we have to do tha same
	$_SESSION['sessid']['roles'] = array();
	$_SESSION['sessid']['roles'][] = 'admin';
	$_SESSION['sessid']['roles'][] = 'global-admin';
	$_SESSION['sessid']['username'] = $p['user'];
	$_SESSION['PFA_token'] = false;
	if(isset($p['token']) && strlen($p['token'])==64 && $p['token']==decrypt(file_get_contents(ADMIN_TOKEN_FILE))){
		$_SESSION['PFA_token'] = $p['token'];
	}elseif(isset($p['user']) && isset($p['pass'])){
		$h = new AdminHandler;
		if ($h->login($p['user'], $p['pass'])) {
			session_regenerate_id();
			
			// 1852673427797059126777135760139006525652319754650249024631321344126610074238976 combinations :p
			$_SESSION['PFA_token'] = md5(uniqid(rand(), true)).md5(uniqid(rand(), true));
			
			$r['token'] = $_SESSION['PFA_token'];
			$h->init($p['user']);
			file_put_contents(ADMIN_TOKEN_FILE,encrypt($_SESSION['PFA_token']));
		}
	}
	return($r);
}

function cast_values($p){
	$c = array();
	$c['int'] = array('mailboxes','quota','aliases');
	$c['string'] = array('description','search','domain','email','localpart','goto','aliasdomain','target_domain','mailbox','from','name');
	$c['bool'] = array('active','default_aliases','show_emails');
	
	
	foreach($c as $cast_to => $arr){
		foreach($arr as $key){
			if(isset($p[$key])){
				switch($cast_to){
					case 'int':
						$p[$key] = is_numeric($p[$key]) ? (int)$p[$key] : 'error';
					break;
					case 'string':
						$p[$key] = (string)$p[$key];
					break;
					case 'bool':
						$p[$key] = (bool)$p[$key];
					break;
				}
			}
		}
	}
	return($p);
}



//Main switch for available calls
function process_api(){
	
	//Initialize our response
	$r = array();
	$r['code'] = 99;
	$r['status'] = 'fail';
	
	//Enforce REQUEST Method
	if($_SERVER['REQUEST_METHOD']==REQUEST_METHOD){
		//Init post var ($_POST is difficult to type with one hand while smoking)
		switch(REQUEST_METHOD){
			case 'POST':
				$p = $_POST;
			break;
			case 'GET':
				$p = $_GET;
			break;
			default:
				$p = array();
			break;
		}
		
		//Make sure we have something to play with
		if(!empty($p)){
			// Convert to the correct types
			$p = cast_values($p);
			
			//Make sure we have a call
			if(isset($p['call'])){
				//Validate the login info: user/pass or token
				$r = handle_login($p,$r);
				
				//Are we in?
				if($_SESSION['PFA_token']){
					
					$p['token'] = $_SESSION['PFA_token'];
					
					//Cases for api calls - kept separate for customization rather than $p['call']($p,$r)
					switch($p['call']){
						// Login
						case 'login':
							$r['status'] = 'ok';
							$r['code'] = 0;
						break;

						// Domains
						case 'create_domain':
							$r = create_domain($p,$r);
						break;
						
						case 'list_domain':
							$r = list_domain($p,$r);
						break;
						
						case 'list_domain_info':
							$r = list_domain_info($p,$r);
						break;
						
						case 'create_aliasdomain':
							$r = create_aliasdomain($p,$r);
						break;
						
						case 'delete_domain':
							$r = delete_domain($p,$r);
						break;
						
						// Mailboxes
						case 'create_mailbox':
							$r = create_mailbox($p,$r);
						break;

						case 'list_mailboxes':
							$r = list_mailboxes($p,$r);
						break;
						
						case 'delete_mailbox':
							$r = delete_mailbox($p,$r);
						break;
						
						// Aliases
						case 'create_alias':
							$r = create_alias($p,$r);
						break;

						case 'list_alias':
							$r = list_alias($p,$r);
						break;
						
						// Active
						case 'edit_active':
							$r = edit_active($p,$r);
						break;
						
						// Emails
						case 'send_email':
							$r = send_email($p,$r);
						break;
						
						case 'send_broadcast':
							$r = send_broadcast($p,$r);
						break;
						
						default:
							$r['code'] = 94;
							$r['errors'][] = 'Unrecognized call';
						break;
					}
				}else{
					$r['code'] = 95;
					$r['errors'][] = 'Not authenticated';
				}
			}else{
				$r['code'] = 96;
				$r['errors'][] = 'Incorrect parameters';
			}
		}else{
			$r['code'] = 97;
			$r['errors'][] = 'Empty request';
		}
	}else{
		$r['code'] = 98;
		$r['errors'][] = 'Incorrect request method';
	}

	
	switch(strtolower(OUTPUT_FORMAT)){
		case 'json':
			echo json_encode($r);
		break;

		case 'json_pretty':
			header("Content-Type:text/plain");
			echo json_encode($r,JSON_PRETTY_PRINT);
		break;

		case 'pre':
			echo pre($r);
		break;
	}
}


//Generic output
function pre($a){
	echo '<pre>';
	print_r($a);
	echo '</pre>';
}
process_api();
?>
