<?php


function create_aliasdomain($p,$r){
	//Define requirements
	$required = array();
	$required['active'] = array('type'=>'bool','desc'=>'Activate or deactivate the alias domain');
	$required['alias_domain'] = array('type'=>'domain','desc'=>'alias domain');
	$required['target_domain'] = array('type'=>'domain','desc'=>'target domain');


	//Check requirements
	$r = validate_fields($required,$p,$r);
	if(!isset($r['errors'])){
		// Setup edit
		$edit_fields = array();
		$edit_fields['table'] = 'aliasdomain';
		
		// Set values
		$edit_fields['values']['active'] = $p['active']; 	//bool
		$edit_fields['values']['alias_domain'] = $p['alias_domain'];		//domain
		$edit_fields['values']['target_domain'] = $p['target_domain'];		//domain

		// Execute edit
		$r = perform_edit($edit_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}


function edit_aliasdomain($p,$r){
	//Define requirements
	$required = array();
	$required['domain'] = array('type'=>'domain_nolookup','desc'=>'existing domain name');
	$required['target_domain'] = array('type'=>'domain_nolookup','desc'=>'existing target domain name');
	$required['active'] = array('type'=>'bool','desc'=>'Activate or deactivate the alias');

	//Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$edit_fields = array();
		$edit_fields['table'] = 'aliasdomain';
		$edit_fields['edit'] = $p['domain'];
		
		// Set values
		$edit_fields['values']['domain'] = $p['domain'];	//domain.com
		$edit_fields['values']['target_domain'] = $p['target_domain'];	//domain.com
		$edit_fields['values']['active'] = $p['active']; 	//bool

		// Execute edit
		$r = perform_edit($edit_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}


function delete_aliasdomain($p,$r){
	// Define requirements
	$required = array();
	$required['aliasdomain'] = array('type'=>'domain','desc'=>'alias domain to delete');

	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$delete_fields = array();
		$delete_fields['table'] = 'aliasdomain';

		// Set values
		$delete_fields['delete'] = $p['aliasdomain'];
		
		// Execute edit
		$r = perform_delete($delete_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}

function activate_aliasdomain($p,$r){ //aliasdomain,active
	// Define requirements
	$required = array();
	$required['aliasdomain'] = array('type'=>'domain','desc'=>'aliasdomain name to activate/inactivate');
	$required['active'] = array('type'=>'bool','desc'=>'activation');

	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		// Setup edit
		$activation_fields = array();
		$activation_fields['table'] = 'aliasdomain';
		$activation_fields['id'] = $p['aliasdomain'];
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