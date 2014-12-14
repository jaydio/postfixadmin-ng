<?php


function create_alias($p,$r){
	// Set defaults
	$p['active'] = isset($p['active']) ? $p['active'] : true;
	//Define requirements
	$required = array();
	$required['domain'] = array('type'=>'domain_nolookup','desc'=>'existing domain name');
	$required['localpart'] = array('type'=>'email_slug','desc'=>'first part of email');
	$required['goto'] = array('type'=>'email_list','desc'=>'full email address');
	$required['active'] = array('type'=>'bool','desc'=>'Active or inactive alias');
	
	//Check requirements
	$r = validate_fields($required,$p,$r);
	if(!isset($r['errors'])){
		// Setup edit
		$edit_fields = array();
		$edit_fields['call'] = 'edit';
		$edit_fields['table'] = 'alias';
		
		// Set values
		$edit_fields['values']['domain'] = $p['domain'];						//domain
		$edit_fields['values']['localpart'] = $p['localpart'];				//[info]@domain.com
		$edit_fields['values']['goto'] = $p['goto'];							//Destination email address
		$edit_fields['values']['active'] = $p['active'];						//bool
		

		// Execute edit
		$r = perform_edit($edit_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}

function edit_ailias($p,$r){
	//Define requirements
	$required = array();
	$required['domain'] = array('type'=>'domain_nolookup','desc'=>'existing domain name');
	$required['from'] = array('type'=>'email','desc'=>'from full email address');
	$required['goto'] = array('type'=>'email_list','desc'=>'to full email address list');
	$required['active'] = array('type'=>'bool','desc'=>'Activate or deactivate the alias');


	//Check requirements
	$r = validate_fields($required,$p,$r);
	if(!isset($r['errors'])){
		// Setup edit
		$edit_fields = array();
		$edit_fields['table'] = 'alias';
		$edit_fields['call'] = 'edit';
		$edit_fields['edit'] = $p['from'];
		
		// Set values
		$edit_fields['values']['active'] = $p['active']; 	//bool
		$edit_fields['values']['domain'] = $p['domain'];	//domain.com
		$edit_fields['values']['goto'] = $p['goto'];		//list separated with "\r\n"

		// Execute edit
		$r = perform_edit($edit_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}

function delete_alias($p,$r){
	// Define requirements
	$required = array();
	$required['alias'] = array('type'=>'email','desc'=>'email alias to delete');

	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$delete_fields = array();
		$delete_fields['table'] = 'alias';

		// Set values
		$delete_fields['delete'] = $p['alias'];
		
		// Execute edit
		$r = perform_delete($delete_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}
	
	return($r);
}

function activate_alias($p,$r){ //alias,active
	// Define requirements
	$required = array();
	$required['alias'] = array('type'=>'email','desc'=>'alias name to activate/inactivate');
	$required['active'] = array('type'=>'bool','desc'=>'activation');

	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$activation_fields = array();
		$activation_fields['table'] = 'alias';
		$activation_fields['id'] = $p['alias'];
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