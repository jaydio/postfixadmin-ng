<?php


function list_domain_info($p,$r){
	//Pull in a few variables from PFA
	global $CONF;
	global $PALANG;

	$admin_username = authentication_get_username();
	$handler = new DomainHandler(0, $admin_username);
	$handler->getList('');
	$result = $handler->result();
	foreach($result as $i => $res){
		$result[$i]['created'] = $result[$i]['_created']; unset($result[$i]['_created']);
		$result[$i]['modified'] = $result[$i]['_modified']; unset($result[$i]['_modified']);
		unset($result[$i]['_active']);
		unset($result[$i]['_backupmx']);
	}
	$r['code'] = 0;
	$r['status'] = 'ok';
	$r['response'] = $result;

	return($r);

}

function list_domain($p,$r){
	$r['code'] = 0;
	$r['status'] = 'ok';
	$r['response'] = list_domains();
	return($r);
}

function list_alias($p,$r){
	// Set defaults
	$p['domain'] = isset($p['domain']) ? $p['domain'] : '';
	$p['search'] = isset($p['search']) ? $p['search'] : '';

	// Define requirements
	$required = array();
	// $required['delete'] = array('type'=>'domain','desc'=>'domain name to delete');


	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		//Pull in a few variables from PFA
		global $CONF;
		global $PALANG;


		//Optional vars
		// search string part of domain - shows all aliases matching search
		// domain string whole domain - shows all aliases fromm domain if found
		$admin_username = authentication_get_username();
		$list_domains = list_domains_for_admin($admin_username);
		$table_alias = table_by_key('alias');

		if(!empty($p['domain']) || !empty($p['search'])){
			if(count($list_domains)){
				if((!empty($p['domain']) && in_array($p['domain'], $list_domains)) || !empty($p['search'])) {
					if ($p['search'] == "") {
						$list_param = "domain='".$p['domain']."'";
						$sql_domain = " $table_alias.domain='".$p['domain']."' ";
					} else {
						$list_param = "(address LIKE '%".$p['search']."%' OR goto LIKE '%".$p['search']."%')";
						$sql_domain = db_in_clause("$table_alias.domain", $list_domains);
					}

					$handler = new AliasHandler(0, $admin_username);
					$handler->getList($list_param);
					$result = $handler->result();
					foreach($result as $i => $res){
						$result[$i]['created'] = $result[$i]['_created']; unset($result[$i]['_created']);
						$result[$i]['modified'] = $result[$i]['_modified']; unset($result[$i]['_modified']);
						unset($result[$i]['_active']);
						unset($result[$i]['__mailbox_username']);
						unset($result[$i]['is_mailbox']);
						unset($result[$i]['editable']);
						unset($result[$i]['goto_mailbox']);
					}
					if(empty($result)){
						$r['code'] = 1;
						$r['status'] = 'ok';
						$r['response'] = $result;
					}else{
						$r['code'] = 0;
						$r['status'] = 'ok';
						if($p['search'] == ""){
							$r['domain_limits'] =  get_domain_properties($p['domain']);
						}
						$r['response'] = $result;
					}

				}else{
					$r['code'] = 2;
					$r['status'] = 'ok';
					$r['response'] = 'Domain not found';
				}
			}else{
				//just show available domains
				$r['code'] = 3;
				$r['status'] = 'ok';
				$r['response'] = false;
			}
		}else{
			$r['code'] = 4;
			$r['status'] = 'ok';
			$r['domains'] = $list_domains;
			$r['response'] = false;
		}
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}
	return($r);
}

function list_mailboxes($p,$r){
	// Set defaults
	$p['domain'] = isset($p['domain']) ? $p['domain'] : '';
	$p['search'] = isset($p['search']) ? $p['search'] : '';

	// Define requirements
	$required = array();
	// $required['delete'] = array('type'=>'domain','desc'=>'domain name to delete');


	// Check requirements
	$r = validate_fields($required,$p,$r);
	
	if(!isset($r['errors'])){
		//Pull in a few variables from PFA
		global $CONF;
		global $PALANG;

		
		$admin_username = authentication_get_username();
		$list_domains = list_domains_for_admin($admin_username);
		$table_mailbox = table_by_key('mailbox');

		$sql_select = "SELECT $table_mailbox.* ";
		$sql_from	= " FROM $table_mailbox ";
		$sql_join	= "";
		$sql_where  = " WHERE ";
		$sql_order  = " ORDER BY $table_mailbox.username ";

		if ($p['search'] == "") {
			$sql_where  .= " $table_mailbox.domain='".$p['domain']."' ";
		} else {
			$sql_where  .=  db_in_clause("$table_mailbox.domain", $list_domains) . " ";
			$sql_where  .= " AND ( $table_mailbox.username LIKE '%".$p['search']."%' OR $table_mailbox.name LIKE '%".$p['search']."%' ";
			$sql_where  .= " ) "; # $p['search'] is already escaped
		}

		if (Config::bool('vacation_control_admin')) {
			$table_vacation = table_by_key('vacation');
			$sql_select .= ", $table_vacation.active AS vacation_active ";
			$sql_join	.= " LEFT JOIN $table_vacation ON $table_mailbox.username=$table_vacation.email ";
		}

		if (Config::bool('used_quotas') && Config::bool('new_quota_table')) {
			$table_quota2 = table_by_key('quota2');
			$sql_select .= ", $table_quota2.bytes as current ";
			$sql_join	.= " LEFT JOIN $table_quota2 ON $table_mailbox.username=$table_quota2.username ";
		}

		if (Config::bool('used_quotas') && ( ! Config::bool('new_quota_table') ) ) {
			$table_quota = table_by_key('quota');
			$sql_select .= ", $table_quota.current ";
			$sql_join	.= " LEFT JOIN $table_quota ON $table_mailbox.username=$table_quota.username ";
			$sql_where  .= " AND ( $table_quota.path='quota/storage' OR  $table_quota.path IS NULL ) ";
		}

		$mailbox_pagebrowser_query = "$sql_from\n$sql_join\n$sql_where\n$sql_order" ;
		$query = "$sql_select\n$mailbox_pagebrowser_query";
		$result = db_query ($query);

		$tMailbox = array();
		if($result['rows'] > 0){
			while ($row = db_array($result['result'])){
				foreach($row as $key => $value){
					if(is_numeric($key)) unset($row[$key]);
				}
				$tMailbox[] = $row;
				$p['domain'] = empty($p['domain']) ? $row['domain'] : $p['domain'];
			}
			$r['code'] = 0;
			$r['status'] = 'ok';
			if ($p['search'] == ""){
				$r['domain_limits'] = get_domain_properties($p['domain']);
			}
			$r['response'] = $tMailbox;
			
		}else{
			$r['code'] = 1;
			$r['status'] = 'ok';
			$r['domains'] = $list_domains;
			$r['response'] = array();
		}
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}
?>