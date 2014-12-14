<?php



function create_mailbox($p,$r){
	// Set defaults
	$p['active'] = isset($p['active']) ? $p['active'] : true;
	$p['name'] = isset($p['name']) ? $p['name'] : '';


	// Define requirements
	$required = array();
	$required['domain'] = array('type'=>'domain','desc'=>'domain name');
	$required['local_part'] = array('type'=>'email_slug','desc'=>'email address slug');
	$required['name'] = array('type'=>'str','desc'=>'mailbox owner name');
	$required['password'] = array('type'=>'str','desc'=>'password');
	$required['password2'] = array('type'=>'str','desc'=>'password confirmation');
	$required['quota'] = array('type'=>'int','desc'=>'mailbox quota mb');
	$required['active'] = array('type'=>'bool','desc'=>'mailbox active');

	
	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	$p['password'] = isset($p['password']) ? $p['password'] : '';
	$p['password2'] = isset($p['password2']) ? $p['password2'] : '';
	if($p['password']!=$p['password2']){
		$r['errors']['password_match'] = 'Passwords do not match';
	}
	
	if(!isset($r['errors'])){
		// Setup edit
		$edit_fields = array();
		$edit_fields['call'] = 'edit';
		$edit_fields['table'] = 'mailbox';

		// Set values
		$edit_fields['values']['domain'] = $p['domain'];
		$edit_fields['values']['local_part'] = $p['local_part'];
		$edit_fields['values']['name'] = $p['name'];
		$edit_fields['values']['password'] = $p['password'];
		$edit_fields['values']['password2'] = $p['password2'];		//see $CONF['password_validation']
		$edit_fields['values']['quota'] = $p['quota'];		//domain quota in MB
		$edit_fields['values']['active'] = $p['active']; 	//bool
		
		// Execute edit
		$r = perform_edit($edit_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}
	
	return($r);
}

function edit_mailbox($p,$r){
	// Set defaults
	$p['password'] = isset($p['password']) ? $p['password'] : '';
	$p['password2'] = isset($p['password2']) ? $p['password2'] : '';
	$p['quota'] = isset($p['quota']) ? intval($p['quota']) : '';
	$p['active'] = isset($p['active']) ? $p['active'] : true;


	// Define requirements
	$required = array();
	$required['email'] = array('type'=>'email','desc'=>'email address');
	$required['name'] = array('type'=>'str','desc'=>'mailbox owner name');
	$required['password'] = array('type'=>'str','desc'=>'password');
	$required['password2'] = array('type'=>'str','desc'=>'password confirmation');
	$required['quota'] = array('type'=>'int','desc'=>'mailbox quota mb');
	$required['active'] = array('type'=>'bool','desc'=>'mailbox active');

	
	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if($p['password']!=$p['password2']){
		$r['errors']['password_match'] = 'Passwords do not match';
	}
	
	if(!isset($r['errors'])){
		// Setup edit
		$edit_fields = array();
		$edit_fields['call'] = 'edit';
		$edit_fields['table'] = 'mailbox';
		$edit_fields['edit'] = $p['email'];

		// Set values

		$edit_fields['values']['name'] = $p['name'];
		$edit_fields['values']['password'] = $p['password'];
		$edit_fields['values']['password2'] = $p['password2'];		//see $CONF['password_validation']
		$edit_fields['values']['quota'] = $p['quota'];		//domain quota in MB
		$edit_fields['values']['active'] = $p['active']; 	//bool
		
		// Execute edit
		$r = perform_edit($edit_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}
	
	return($r);
}

function delete_mailbox($p,$r){

	// Define requirements
	$required = array();
	$required['mailbox'] = array('type'=>'email','desc'=>'mailbox email to delete');


	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$delete_fields = array();
		$delete_fields['table'] = 'mailbox';

		// Set values
		$delete_fields['delete'] = $p['mailbox'];
		
		// Execute edit
		$r = perform_delete($delete_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}
	return($r);
}

function activate_mailbox($p,$r){ //mailbox (email),active
	// Define requirements
	$required = array();
	$required['mailbox'] = array('type'=>'email','desc'=>'mailbox name to activate/inactivate');
	$required['active'] = array('type'=>'bool','desc'=>'activation');

	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$activation_fields = array();
		$activation_fields['table'] = 'mailbox';
		$activation_fields['id'] = $p['mailbox'];
		$activation_fields['active'] = 	$p['active'];

		// Execute edit
		$r = edit_active($activation_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}
	return($r);
}


?>