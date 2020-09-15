<?php

namespace App\Service;

/**
 * Google Cloud Service Class
 * @author Rormdar Zavala <rzavala@praga.ws>
 */
class FCMService
{
	const API_URL = 'https://fcm.googleapis.com/fcm/send';

	protected $fcmServerKey;

	/**
	 * GCMService constructor.
	 * @param string $fcmServerKey
	 */
	public function __construct($fcmServerKey)
	{
		$this->fcmServerKey = $fcmServerKey;
	}

    /**
     * Send GCM Request
     * @param array $notification
     * @param array $data
     * @param array $ids
     * @return array
     */
	public function send(array $notification, array $data, array $ids)
	{
		if (!$this->_isCurl()) {
			return ['error' => 'cURL must be active in this system.'];
		}

		$apiKey = $this->fcmServerKey;
		$post = ['registration_ids' => $ids, 'notification' => $notification, 'data' => $data];
		$headers = ['Authorization: key=' . $apiKey, 'Content-Type: application/json'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, FCMService::API_URL);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			return ['error' => curl_error($ch)];
		}

		curl_close($ch);

		return json_decode($result, true);
	}

	private function _isCurl()
	{
		return function_exists('curl_version');
	}
}
