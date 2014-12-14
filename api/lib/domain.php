<?php


function create_domain($p,$r){
	// Set defaults
	$p['active'] = isset($p['active']) ? $p['active'] : true;
	$p['default_aliases'] = isset($p['default_aliases']) ? $p['default_aliases'] : true;
	$p['description'] = isset($p['description']) ? $p['description'] : '';
	
	//Define requirements
	$required = array();
	$required['domain'] = array('type'=>'domain','desc'=>'domain name');
	$required['aliases'] = array('type'=>'int','desc'=>'number of aliases');
	$required['mailboxes'] = array('type'=>'int','desc'=>'number of mailboxes');
	$required['quota'] = array('type'=>'int','desc'=>'quota');
	$required['active'] = array('type'=>'bool','desc'=>'active');
	$required['default_aliases'] = array('type'=>'bool','desc'=>'default aliases');


	// Check requirements
	$r = validate_fields($required,$p,$r);
	if(!isset($r['errors'])){
		// Setup edit
		$edit_fields = array();
		$edit_fields['table'] = 'domain';

		// Set values
		$edit_fields['values']['domain'] = $p['domain'];
		$edit_fields['values']['description'] = $p['description'];	//not required, text
		$edit_fields['values']['aliases'] = $p['aliases'];		//limit aliases (-1 no aliases, 0 unlimited, 10 = 10)
		$edit_fields['values']['mailboxes'] = $p['mailboxes'];		//limit mailboxes (-1 no aliases, 0 unlimited, 10 = 10)
		$edit_fields['values']['quota'] = $p['quota'];		//domain quota in MB
		$edit_fields['values']['active'] = $p['active']; 	//bool
		$edit_fields['values']['default_aliases'] = $p['default_aliases']; //add abuse, postmaster etc/always
		
		// Execute edit
		$r = perform_edit($edit_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}

function edit_domain($p,$r){
	// Set defaults
	$p['active'] = isset($p['active']) ? $p['active'] : true;
	$p['description'] = isset($p['description']) ? $p['description'] : '';
	
	//Define requirements
	$required = array();
	$required['domain'] = array('type'=>'domain','desc'=>'domain name');
	$required['aliases'] = array('type'=>'int','desc'=>'number of aliases');
	$required['mailboxes'] = array('type'=>'int','desc'=>'number of mailboxes');
	$required['quota'] = array('type'=>'int','desc'=>'quota');
	$required['active'] = array('type'=>'bool','desc'=>'active');


	// Check requirements
	$r = validate_fields($required,$p,$r);
	if(!isset($r['errors'])){
		// Setup edit
		$edit_fields = array();
		$edit_fields['table'] = 'domain';
		$edit_fields['edit'] = $p['domain'];

		// Set values
		$edit_fields['values']['description'] = $p['description'];	//not required, text
		$edit_fields['values']['aliases'] = $p['aliases'];		//limit aliases (-1 no aliases, 0 unlimited, 10 = 10)
		$edit_fields['values']['mailboxes'] = $p['mailboxes'];		//limit mailboxes (-1 no aliases, 0 unlimited, 10 = 10)
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

function delete_domain($p,$r){
	// Define requirements
	$required = array();
	$required['domain'] = array('type'=>'domain_nolookup','desc'=>'domain name to delete');

	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$delete_fields = array();
		$delete_fields['table'] = 'domain';

		// Set values
		$delete_fields['delete'] = $p['domain'];
		
		// Execute edit
		$r = perform_delete($delete_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}
	
	return($r);
}

function activate_domain($p,$r){ //domain,active
	// Define requirements
	$required = array();
	$required['domain'] = array('type'=>'domain_nolookup','desc'=>'domain name to activate/inactivate');
	$required['active'] = array('type'=>'bool','desc'=>'activation');

	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$activation_fields = array();
		$activation_fields['table'] = 'domain';
		$activation_fields['id'] = $p['domain'];
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