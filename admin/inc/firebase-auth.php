<?php
function firebase_auth_request($endpoint, $payload)
{
	$api_key = getenv('FIREBASE_API_KEY');
	if (!$api_key) {
		return array('ok' => false, 'error' => 'Firebase is not configured.');
	}
	if (!function_exists('curl_init')) {
		return array('ok' => false, 'error' => 'The PHP cURL extension is required.');
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