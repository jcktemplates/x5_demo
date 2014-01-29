<?php

include ("../res/x5engine.php");
$ecommerce = new ImCart();
$ecommerce->setSettings(array(
	'force_sender' => false,
	'email_opening' => 'Gentile Cliente,<br /><br />Ringraziandola per il Suo acquisto le inviamo il riepilogo del suo Ordine.<br /><br />Qui di seguito può trovare l\'elenco dei prodotti ordinati, i dati di fatturazione e le istruzioni per la spedizione ed il pagamento scelto.',
	'email_closing' => 'Rimaniamo a Sua disposizione per ulteriori informazioni.<br /><br />Cordiali Saluti, Staff Commerciale.',
	'useCSV' => false,
	'header_bg_color' => '#808080',
	'header_text_color' => '#FFFFFF',
	'cell_bg_color' => '#FFFFFF',
	'cell_text_color' => '#000000',
	'border_color' => '#808080',
	'owner_email' => 'mail@mail.it',
	'vat_type' => 'included'
));

if (@$_GET['action'] == 'sndrdr' && isset($_POST['orderData'])) {
	$orderNo = $_POST['orderData']['orderNo'];
	$ecommerce->setOrderData($_POST['orderData']);
	$ecommerce->sendOwnerEmail();
	$ecommerce->sendCustomerEmail();
	header('Content-type: application/json');
	echo '{ "orderNo": "' . $orderNo . '" }';
	exit();
}

// End of file x5cart.php