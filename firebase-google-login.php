<?php
session_start();
require_once('admin/inc/config.php');
require_once('admin/inc/firebase-auth.php');
header('Content-Type: application/json; charset=utf-8');

function google_login_response($status, $payload)
{
	http_response_code($status);
	echo json_encode($payload);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	google_login_response(405, array('error' => 'Method not allowed.'));
}

$csrf_token = isset($_POST['_csrf']) ? $_POST['_csrf'] : '';
if (!isset($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $csrf_token)) {
	google_login_response(403, array('error' => 'Your session expired. Refresh the page and try again.'));
}

$id_token = isset($_POST['idToken']) ? $_POST['idToken'] : '';
if ($id_token === '') {
	google_login_response(400, array('error' => 'Google sign-in did not return a valid credential.'));
}

$firebase_result = firebase_auth_request('accounts:lookup', array('idToken' => $id_token));
if (!$firebase_result['ok'] || empty($firebase_result['data']['users'][0])) {
	$error = isset($firebase_result['error']) ? firebase_auth_error_message($firebase_result['error'], 'google') : 'Google sign-in could not be verified.';
	google_login_response(401, array('error' => $error));
}

$firebase_user = $firebase_result['data']['users'][0];
$email = isset($firebase_user['email']) ? strtolower($firebase_user['email']) : '';
$email_domain = substr(strrchr($email, '@'), 1);
$google_provider = false;
if (isset($firebase_user['providerUserInfo']) && is_array($firebase_user['providerUserInfo'])) {
	foreach ($firebase_user['providerUserInfo'] as $provider_info) {
		if (isset($provider_info['providerId']) && $provider_info['providerId'] === 'google.com') {
			$google_provider = true;
			break;
		}
	}
}

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || !in_array($email_domain, array('gmail.com', 'tip.edu.ph'), true)) {
	google_login_response(403, array('error' => 'Use a Gmail address or a TIP school email address.'));
}
if (empty($firebase_user['emailVerified']) || !$google_provider) {
	google_login_response(403, array('error' => 'Use a verified Google account to continue.'));
}

try {
	$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=? LIMIT 1");
	$statement->execute(array($email));
	$customer = $statement->fetch(PDO::FETCH_ASSOC);

	if (!$customer) {
		$display_name = isset($firebase_user['displayName']) ? trim(strip_tags($firebase_user['displayName'])) : '';
		if ($display_name === '') {
			$display_name = strstr($email, '@', true);
		}
		$statement = $pdo->prepare("INSERT INTO tbl_customer (
			cust_name,
			cust_cname,
			cust_email,
			cust_phone,
			cust_address,
			cust_password,
			cust_token,
			cust_datetime,
			cust_timestamp,
			cust_status,
			cust_email_verified
		) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
		$statement->execute(array(
			$display_name,
			'',
			$email,
			'',
			'',
			md5(bin2hex(random_bytes(32))),
			bin2hex(random_bytes(32)),
			date('Y-m-d H:i:s'),
			time(),
			1,
			1
		));
		$customer_id = $pdo->lastInsertId();
		$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=?");
		$statement->execute(array($customer_id));
		$customer = $statement->fetch(PDO::FETCH_ASSOC);
	} elseif ((int)$customer['cust_status'] !== 1) {
		google_login_response(403, array('error' => 'This account is currently disabled. Contact the store administrator.'));
	} else {
		$statement = $pdo->prepare("UPDATE tbl_customer SET cust_email_verified=1 WHERE cust_id=?");
		$statement->execute(array($customer['cust_id']));
		$customer['cust_email_verified'] = 1;
	}

	if (!$customer) {
		google_login_response(500, array('error' => 'Could not create your customer profile.'));
	}

	session_regenerate_id(true);
	$_SESSION['customer'] = $customer;
	google_login_response(200, array('redirect' => BASE_URL . 'dashboard.php'));
} catch (PDOException $exception) {
	google_login_response(500, array('error' => 'Could not complete Google sign-in. Please try again.'));
}