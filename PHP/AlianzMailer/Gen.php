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
use \AlianzMailer\Dispatcher;
use \AlianzMailer\Messenger\Receipient;

/**
 * Main Email Generator
 * @package AlianzMailer
 * @author AlianzMail
*/
class Gen
{
	/**
	 * sender email
	 * required
	 * @var string from_email
	 */
	public $from_email;
	/**
	 * sender name
	 * required
	 * @var string from_name
	 */
	public $from_name;
	
	/**
	 * return address email
	 * optional
	 * @var string reply_to_email
	 */
	public $reply_to_name;
	/**
	 * return address name
	 * optional
	 * @var string reply_to_name
	 */
	public $reply_to_email;
	
	/**
	 * email mesengers
	 * atleast one messenger should be defined
	 * @var array $_messengers
	 */
	public $_messengers;

	/**
	 * General message subject
	 * May be replaced with internal subject of each messenger
	 * required
	 * @var String $subject
	 */
	public $subject;
	/**
	 * Time to send message
	 * optional
	 * @var string $dispatch_time
	 */
	public $dispatch_time;

	/**
	 * Actual message object
	 * May be overwritten by internal message of any messenger
	 * 
	 * @var Message $message
	 */
	protected $message;

	/**
	 * compiled message to send
	 * 
	 * @var array $compiled
	 */
	protected $compiled = array();


	function __construct()
	{
		$this->message = new Message;
	}
	
	/**
	 * Sets the sender of the email
	 * @param string $email email address of sender. should be registered domain email
	 * @param string|null $name name of sender
	 * @return Gen
	 */
	public function setFrom($email,$name = null )
	{
		$this->from_email = $email;
		$this->from_name = $name;
		return $this;
	}

	/**
	 * set general subject of email
	 * @param string $subject email subject
	 * @return Gen
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * set return address and name of email batch
	 * @param string $email email of sender
	 * @param string|null $name name of sender
	 * @return Gen
	 */
	public function setReplyTo($email,$name = null)
	{
		$this->reply_to_email = $email;
		$this->reply_to_name = $name;
		return $this;
	}
	
	/**
	 * set email send time in mysql db format
	 * YYY-MM-DD HH:MM:SS
	 * <code>
	 * 	$Gen->setDispatchTime('2012-12-23 12:12:2');
	 * </code>
	 * @param string $dispatch_time timestring
	 * @return Gen
	 */
	public function setDispatchTime($dispatch_time)
	{
		$this->dispatch_time = $dispatch_time;
		return $this;
	}

	/**
	 * Add new messenger object to the list of messengers
	 * <code>
	 * 	$Gen = new \AlianzMailer\Gen;
	 * 	$msg_obj = new \AlianzMailer\Messenger;
	 * 	// set messenger options
	 * 	$Gen->addMessenger($msg_obj);
	 * </code>
	 * @param Messenger $messenger messenger object
	 * @see \AlianzMailer\Messenger for how to set messenger options
	 * @return Gen
	 */
	public function addMessenger(Messenger $messenger)
	{
		$this->_messengers[$messenger->getName()] = $messenger;
		return $this;
	}

	/**
	 * Let Alianz Create a new messenger object and 
	 * add to the list based on supplied array parameters
	 * <code>
	 * $Gen->createMessenger(array(
	 * 	'subject'=>'some subject',
	 * 	'dispatch_time' =>'YYY-MM-DD',
	 * 	'name'		=> 'unique_name',
	 * 	'receipients' => array(array(
	 * 		'email'=>'someone@somedomain',
	 * 		'name' => 'someoptional name',
	 * 		'type' => $Messenger::RCPT_DIRECT
	 * 		)),
	 *  ));
	 * </code>
	 * use 'tos', 'bccs','ccs' instead of 'receipients' to be more specific <br>
	 * 'receipients' require 'type' to be set on each receipient array
	 * @param Array $messenger array of messenger options
	 * @return Gen
	 */
	public function createMessenger(Array $messenger)
	{
		$msg_obj = new Messenger;
		// set the normal props of the messenger
			// subject
			// dispatch_time
			// name

		foreach ($messenger as $field => $value) {
			if (property_exists($msg_obj, $field) && $field != '_receipients') {
				$msg_obj->{$field} = $value;
			}
		}
		// if generalized receipients are received
		// each receipient array should have its own type
		// else direct receipient type will be applied
		if (isset($messenger['receipients'])) {
			$msg_obj->addReceipients($messenger['receipients']);
		}
		// if direct receipients received
		if (isset($messenger['tos'])) {
			$msg_obj->addDirectReceipients($messenger['tos']);
		}
		// if bcarbon copy receipients
		if (isset($messenger['bccs'])) {
			$msg_obj->addBCCReceipients($messenger['bccs']);
		}
		// if carbon copy receipients
		if (isset($messenger['ccs'])) {
			$msg_obj->addCCReceipients($messenger['ccs']);
		}
		$this->_messengers[$msg_obj->getName()] = $msg_obj;
		return $msg_obj;
	}

	/**
	 * Remove a messenger from the set
	 * @param string $name name of the messenger
	 * @link Messenger::setName();
	 * @return Gen
	 */
	public function dropMessenger($name)
	{
		if (isset($this->_messengers[$name])) {
			unset($this->_messengers[$name]);
		}
		return $this;
	}
	/**
	 * Get a single messenger from your defined list
	 * @param string $name name of the messenger
	 * @return Messenger
	 */
	public function getMessenger($name)
	{
		if (isset($this->_messengers[$name])) {
			return $this->_messengers[$name];
		}
		return false;
	}


	/**
	 * Get all messengers
	 * @return Array
	 */
	public function getMessengers()
	{
		return $this->_messengers;
	}

	/**
	 * Set html message to send
	 * @param mixed $html your prepared html message to send
	 * @uses Message::setBody() to set the html in the message object
	 * @return Gen
	 */
	public function htmlBody($html)
	{
		$this->message->setBody($html);
		return $this;
	}

	/**
	 * set alternate normal text message
	 * this is required even if you are sending html
	 * @param mixed $alt alternate non html message
	 * @return Gen
	 */
	public function altBody($alt)
	{
		$this->message->setAlt($alt);
		return $this;
	}

	/**
	 * Compiles parameters to generate message for sending
	 * returns the results if successfull
	 * @uses Messenger::compile() to compile each messenger
	 * @return Array
	 */
	public function compile()
	{
		if (empty($this->message->getBody())) {
			throw new \Exception("Message body cannot be empty");
		}
		if (empty($this->from_email)) {
			throw new \Exception("Message needs a sender");
		}
		$this->compiled = array(
			'subject' => $this->subject,
			'from'    => array(
					'email' => $this->from_email,
					),
			'message' => array(
				'html'  => $this->message->getBody(),
				'text'  => $this->message->getAlt(),
				),
			);
		if ($this->from_name) {
			$this->compiled['from']['name'] = $this->from_name;
		}
		if ($this->dispatch_time) {
			$this->compiled['dispatch_time'] = $this->dispatch_time;
		}
		if ($this->reply_to_email) {
			$this->compiled['reply_to'] = array(
				'email' => $this->reply_to_email
				);
			if ($this->reply_to_name) {
				$this->compiled['reply_to']['name'] = $this->reply_to_name;
			}
		}
		$this->compiled['messengers'] = array();
		foreach ($this->_messengers as $key => $messenger) {
			$this->compiled['messengers'][] = $messenger->compile();
			
		}
		return $this->compiled;
	}

	/**
	 * Returns the compiled array 
	 * @return Array
	 */
	public function getCompiled()
	{
		return $this->compile();
	}

	/**
	 * set the bearer token for authentication
	 * @param string $token Bearer token
	 * @return Gen
	 */
	public function setCredentials($token)
	{
		$this->token = $token;
		return $this;
	}

	/**
	 * Dispatch the message to the receipients in all defined messengers
	 * @uses Dispatcher to send message
	 * @uses Dispatcher::cred() to set the bearer credentials
	 * @uses Dispatcher::dispatch() to send the message
	 * @return mixed
	 */
	public function dispatch()
	{
		if (!$this->token){
			throw new \Exception("Authorization info not found", 1);
			
		}
		$this->compile();
		$dispatcher = new Dispatcher($this->compiled);
		$dispatcher->cred($this->token);
		$res = $dispatcher->dispatch();
		return $res;
	}
}
