<?php

/**
 * @package AlianzMailer
 * @author AlianzMail
 * @version 1.0.0
 * @copyright AlianzMail 2016 
 * @link http://alianzmail.com/docs
 * 
*/

namespace AlianzMailer;

use \AlianzMailer\Message;
use \AlianzMailer\Messenger;
use \AlianzMailer\Messenger\Receipient;


/**
 * Sends out the emails
 * Receives the message array, creates a curl handler
 * and sends out the emails
 * @package AlianzMailer
 */
class Dispatcher
{
	/**
	 * http headers
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Dispatcher constructor, receives the compiled message array
	 * @param array $message compiled message
	 * @return Dispatcher
	 */
	function __construct($message)
	{
		$this->message = json_encode($message);
		$this->url = 'https://api.alianzmail.com/v1/mail/send';
	}
	/**
	 * Set credential
	 * @param string $token bearer token
	 * @return Dispatcher
	 */
	public function cred( $token )
	{
		$b = 'Authorization: Bearer '.$token;
		$this->headers['Authorization'] = $b;
		return $this;
	}

	/**
	 * Set curl url
	 * should be called before Dispatcher::dispatch()
	 * default has already been defined. no need to change unless very necessary
	 * @param string $url curl url
	 * @return Dispatcher
	 */
	public function url($url)
	{
		$this->url = $url;
	}

	/**
	 * Send out emails
	 * @param string|null $curl curl handler
	 * @return mixed
	 */
	public function dispatch($curl = null)
	{
		$this->headers[] = 'Content-Type:application\json';
		if (is_null($curl)) {
			$curl = curl_init();
			/*curl_setopt($curl, CURLOPT_URL, $this->url);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $this->message);
			curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);*/
			// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_HEADER => true,
				CURLOPT_POSTFIELDS => $this->message,
				CURLOPT_HTTPHEADER => $this->headers,
				));
		}
		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return "cURL Error :" . $err;
		} else {
			if (preg_match('/HTTP(.*) 200 OK/', $response)) {
				return true;
			}
		  return $response;
		}
	}
}