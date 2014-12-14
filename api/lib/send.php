<?php

function send_email($p,$r){
	// Set defaults
	$p['active'] = isset($p['active']) ? $p['active'] : true;
	$p['from'] = isset($p['from']) ? $p['from'] : smtp_get_admin_email();

	//Define requirements
	$required = array();
	$required['to'] = array('type'=>'email','desc'=>'to address');
	$required['subject'] = array('type'=>'str','desc'=>'subject');
	$required['body'] = array('type'=>'str','desc'=>'body');
	$required['from'] = array('type'=>'email','desc'=>'from email address');
	
	//Check requirements
	$r = validate_fields($required,$p,$r);
	if(!isset($r['errors'])){
		// Setup send
		$send_fields = array();

		// Set values
		$send_fields['to'] = $p['to'];							// Destination email address
		$send_fields['subject'] = $p['subject'];				// Email subject
		$send_fields['body'] = $p['body'];						// Email body

		// Execute send
		$r = perform_send($send_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}

function send_broadcast($p,$r){
	// Set defaults
	$p['active'] = isset($p['active']) ? $p['active'] : true;
	$p['from'] = isset($p['from']) ? $p['from'] : smtp_get_admin_email();
	$p['show_emails'] = isset($p['show_emails']) ? $p['show_emails'] : false;

	//Define requirements
	$required = array();
	$required['name'] = array('type'=>'str','desc'=>'from name');
	$required['subject'] = array('type'=>'str','desc'=>'subject');
	$required['body'] = array('type'=>'str','desc'=>'body');
	$required['from'] = array('type'=>'email','desc'=>'from email address');
	$required['show_emails'] = array('type'=>'bool','desc'=>'show emails');
	//Check requirements
	$r = validate_fields($required,$p,$r);

	if(!isset($r['errors'])){
		// Setup send
		$send_fields = array();

		// Set values
		$send_fields['name'] = $p['name'];							// Destination email address
		$send_fields['subject'] = $p['subject'];				// Email subject
		$send_fields['body'] = $p['body'];						// Email body
		$send_fields['from'] = $p['from'];						// Email body
		$send_fields['show_emails'] = $p['show_emails'];						// Email body
		

		// Execute send
		$r = perform_broadcast($send_fields,$r);
	}else{
		$r['info'] = 'Validation error';
		$r['code'] = 93;
	}

	return($r);
}

function perform_send($p,$r){
	$p['from'] = isset($p['from']) ? $p['from'] : smtp_get_admin_email();

	if (get_magic_quotes_gpc ()){
		$p['body'] = stripslashes($p['body']);
	}

	$email_check = check_email($p['to']);
	if($email_check == ''){
		if(1==1||smtp_mail ($p['to'], $p['from'], $p['subject'], $p['body'])) {
			$r['info'] = 'Email sent from: '.$p['from'];
			$r['status'] = 'ok';
			$r['code'] = 0;
		} else {
			$r['errors'][] = 'Error sending mail';
			$r['code'] = 1;
		}
	}else{
		$r['errors'][] = $email_check;
		$r['code'] = 2;
	}
	return($r);
}

function perform_broadcast($p,$r){
	$table_mailbox = table_by_key('mailbox');
	$table_alias = table_by_key('alias');

	$q = "select username from $table_mailbox union select goto from $table_alias " .
	"where goto not in (select username from $table_mailbox)";

	$result = db_query ($q);
	
	if ($result['rows'] > 0){
		mb_internal_encoding("UTF-8");
		$b_name = mb_encode_mimeheader($p['name'], 'UTF-8', 'Q');
		$b_subject = mb_encode_mimeheader($p['subject'], 'UTF-8', 'Q');
		$b_message = base64_encode($p['body']);

		$i = 0;
		$r['response']['total_sent'] = 0;
		$r['response']['total_failed'] = 0;
		$r['response']['sent'] = array();
		$r['response']['failed'] = array();
		while ($row = db_array ($result['result'])){
			$fTo = $row[0];
			$fHeaders  = 'To: ' . $fTo . "\n";
			$fHeaders .= 'From: ' . $b_name . ' <' . $p['from'] . ">\n";
			$fHeaders .= 'Subject: ' . $b_subject . "\n";
			$fHeaders .= 'MIME-Version: 1.0' . "\n";
			$fHeaders .= 'Content-Type: text/plain; charset=UTF-8' . "\n";
			$fHeaders .= 'Content-Transfer-Encoding: base64' . "\n";
			$fHeaders .= $b_message;
			if (smtp_mail($fTo, $p['from'], $fHeaders)){
				$r['response']['sent'][] = $fTo;
			}else{
				$r['response']['failed'][] = $fTo;
			}
		}
		$r['response']['total_sent'] = count($r['response']['sent']);
		$r['response']['total_failed'] = count($r['response']['failed']);
		if($r['response']['total_sent']>0){
			$r['status'] = 'ok';
			$r['code'] = 0;
		}
	}else{
		$r['code'] = 2;
		$r['status'] = 'ok';
		$r['info'] = 'No emails configured yet';
	}
	if(!$p['show_emails']){
		unset($r['response']['sent']);
		unset($r['response']['failed']);
	}
	return($r);
}

?>