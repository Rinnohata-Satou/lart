<?php
function firebase_auth_request($endpoint, $payload)
{
	$api_key = getenv('FIREBASE_API_KEY');
	if (!$api_key) {
		$api_key = 'AIzaSyCKAv02Qh0eFAaNwNq2-xAiyvZEL27W0kU';
	}
	if (!function_exists('curl_init')) {
		return array('ok' => false, 'error' => 'CURL_NOT_AVAILABLE');
	}

	$url = 'https://identitytoolkit.googleapis.com/v1/' . $endpoint . '?key=' . rawurlencode($api_key);
	$curl = curl_init($url);
	curl_setopt_array($curl, array(
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode($payload),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_TIMEOUT => 15
	));
	$response_body = curl_exec($curl);
	$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if ($response_body === false) {
		return array('ok' => false, 'error' => 'Could not reach Firebase.');
	}

	$response = json_decode($response_body, true);
	if ($status_code < 200 || $status_code >= 300 || !is_array($response)) {
		$error_code = '';
		if (isset($response['error']['message'])) {
			$error_code = $response['error']['message'];
		}
		return array('ok' => false, 'error' => $error_code);
	}

	return array('ok' => true, 'data' => $response);
}

function firebase_auth_error_message($error, $action)
{
	$error_code = trim(explode(':', $error, 2)[0]);
	if ($error === 'CURL_NOT_AVAILABLE') {
		return 'The PHP cURL extension is required to connect to Firebase.';
	}
	if (in_array($error_code, array('API_KEY_INVALID', 'API_KEY_SERVICE_BLOCKED', 'API_KEY_HTTP_REFERRER_BLOCKED', 'API_KEY_IP_ADDRESS_BLOCKED', 'PROJECT_NOT_FOUND'), true)) {
		return 'Firebase rejected the server API key. Check that FIREBASE_API_KEY matches the web app config and allows the Identity Toolkit API.';
	}
	if ($error_code === 'OPERATION_NOT_ALLOWED') {
		return $action === 'signup'
			? 'Email/Password sign-in is disabled in Firebase Authentication. Enable it and try again.'
			: 'Google sign-in is disabled in Firebase Authentication. Enable it and try again.';
	}
	if ($error_code === 'WEAK_PASSWORD') {
		return 'Choose a stronger password. Firebase requires at least 6 characters.';
	}
	if ($error_code === 'INVALID_EMAIL') {
		return 'Enter a valid email address and try again.';
	}
	if ($error_code === 'TOO_MANY_ATTEMPTS_TRY_LATER') {
		return 'Too many attempts. Wait a little while, then try again.';
	}
	if ($action === 'google' && in_array($error_code, array('INVALID_ID_TOKEN', 'TOKEN_EXPIRED', 'INVALID_IDP_RESPONSE'), true)) {
		return 'Google sign-in could not be verified. Confirm the browser and server use the same Firebase project, then try again.';
	}

	if (preg_match('/^[A-Z0-9_]+$/', $error_code)) {
		return 'Firebase returned error code ' . $error_code . '. Check the Firebase Authentication setup and try again.';
	}

	return $action === 'signup'
		? 'Firebase sign-up could not be completed. Check the Firebase setup and try again.'
		: 'Google sign-in could not be verified. Check the Firebase setup and try again.';
}