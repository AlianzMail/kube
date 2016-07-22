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

use \AlianzMailer\Messenger\Receipient;


/**
 * Creates a set of email receipients and their message
 * Allows sending multiple messages to similar or different users
 * Ar least one messenger should be set in Gen 
 * @package AlianzMailer
 */
class Messenger
{
	const RCPT_DIRECT = 'direct';
	const RCPT_CC  = 'cc';
	const RCPT_BCC = 'bcc';
	const RCPT_ALL = 'all';

	/**
	 * @var messenger subject
	 */
	public $subject = '';
	/**
	 * Time to send message
	 * @var string
	 */
	public $dispatch_time;
	/**
	 * set of all receipients
	 * @var array
	 */
	public $_receipients = array();
	/**
	 * Unique messenger name
	 * @var string
	 */
	public $name;

	/**
	 * compiled messenger array
	 * @var array
	 */
	public $compiled = array();
	// public $_direct_receipients = array();
	// public $_cc_receipients = array();
	// public $_bcc_receipients = array();

	function __construct($name = null)
	{
		if (is_null($name)) {
			$name = md5(rand(1000,10000));
		}
		$this->setName($name);
	}
	/**
	 * set subject of receipient
	 * @param string $subject receipient subject
	 * @return Gen
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}
	/**
	 * returns the subject of the messenger
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}
	/**
	 * set dispatch time
	 * YYY-MM-DD HH:MM:SS
	 * @param string $subject messenger subject
	 * @return Gen
	 */
	public function setDispatchTime($subject)
	{
		$this->dispatch_time = $dispatch_time;
		return $this;
	}

	/**
	 * Returns dispatch time
	 * @return string
	 */
	public function getDispatchTime()
	{
		return $this->dispatch_time;
	}
	/**
	 * Set a name for the Messenger
	 * @param string $name unique name for the messenger
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	/**
	 * Returns messenger name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Add a receipient to the list of mail receipients
	 * <code>
	 * $Messenger = new Messenger;
	 * $receipient = array('Email'=>'e@m.com','Name'=>'optional');
	 * // or 
	 * $receipient = new Receipient('e@m.com',Messenger::RCPT_DIRECT);
	 * $receipient->setName('optional');
	 * $Messenger->addReceipient($recepient);
	 * </code>
	 * @param Array $receipient receipient set (array or Receipient object)
	 * @param string $type type of receipient
	 * @return mixed
	 */
	private function addReceipient(Array $receipient, $type)
	{
		
		if ( is_array($receipient) && isset($receipient['Email'])) {
			$_receipient = new Receipient($receipient['Email'],$type);
			if (isset($receipient['Name'])) {
				$_receipient->setName($receipient['Name']);
			}
			$this->_receipients[] = $_receipient;

		} elseif (is_object($receipient) && $receipient instanceof Receipient) {
			$this->_receipients[] = $receipient;
			
		} else{
			return false;
		}
		return $this;
	}

	/**
	 * Add a set of receipients
	 * @param Array $receipients array of array of receipients
	 * @param string $type type of receipient
	 * @link self::addReceipient() to add each of the receipients
	 * @return Gen
	 */
	private function addReceipients(Array $receipients, $type)
	{
		foreach ($receipients as $key => $receipient) {
			if (is_array($receipient) && isset($receipient['type'])) {
					$type = $receipient['type'];
			}
			elseif (is_object($receipient) && property_exists($receipient, 'type') ) {
				$type = $receipient->getType();
			}
			$this->addReceipient( $receipient, $type );
		}
		return $this;
	}

	/**
	 * Remove all receipients by type 
	 * if type is all, remove all
	 * @param string $type type of receipients to remove
	 * @return Gen
	 */
	private function clearReceipients($type)
	{
		switch ($type) {
			case self::RCPT_ALL:
				$this->_receipients = array();
				break;
			
			default: //all
				foreach ($this->_receipients as $key => $receipient) {
					if ($receipient->getType() == $type) {
						unset($this->_receipients[$key]);
					}
				}
				break;
		}
		return $this;
	}

	/**
	 * Remove a receipient
	 * @param string $email array of the receipient
	 * @param string $type type of the receipient
	 * @return Gen
	 */
	private function clearReceipient($email, $type)
	{
		foreach ($this->_receipients as $key => $receipient) {
			if ($receipient->getEmail() == $email && $receipient->getType() == $type) {
				unset($this->_receipients[$key]);
				return $this;
			}
		}
	}

	/**
	 * Get receipients by a particular type
	 * or return all if type is all
	 * @param string $type type to look for
	 * @return array
	 */
	private function getReceipients($type)
	{
		switch ($type) {
			case self::RCPT_ALL:
				return $this->_receipients;
				break;
			
			default:
				$subset = array();
				foreach ($this->_receipients as $key => $receipient) {
						if ($receipient->getType() == $type) {
							$subset[] = $receipient;
						}
				}
				return $subset;
				break;
		}
	}

	/**
	 * Compile the messenger to generate sending script
	 * @see Gen::compile() 
	 * @return array
	 */
	public function compile()
	{
		$this->compiled = array(
			'subject' => $this->subject,
			);
		if ($this->dispatch_time) {
			$this->compiled['dispatch_time'] = $this->dispatch_time;
		}
		$tos = array();
		$cc = array();
		$bcc = array();
		foreach ($this->_receipients as $key => $receipient) {
			switch ($receipient->getType()) {
				case self::RCPT_DIRECT:
					$tos[] = $receipient->compile();
					break;

				case self::RCPT_CC:
					$cc[] = $receipient->compile();
					break;

				case self::RCPT_BCC:
					$bcc[] = $receipient->compile();
					break;
			}
		}
		if ($tos) {
			$this->compiled['to'] = $tos;
		}
		if ($cc) {
			$this->compiled['cc'] = $cc;
		}
		if ($bcc) {
			$this->compiled['bcc'] = $bcc;
		}

		return $this->compiled;
	}

	/**
	 * General caller
	 * Any of these functions can be called to perform activity
	 * using the magic method 		<br>
	 * <code>
	 * // create a receipient
	 * $rcpt = new \AlianzMailer\Messenger\Receipeint($email,$type,$name);
	 * // init messenger
	 * $msg = new Messenger();
	 * $msg->addReceipient($rcpt);   		// add a direct receipient
	 * $msg->addDirectReceipient($rcpt);	// add a direct receipient
	 * $msg->addCCReceipient($rcpt);		// add receipient to CC list
	 * $msg->addBCCReceipient($rcpt);		// add receipient to CC list
	 * 
	 * // add set of receipients
	 * 
	 * $msg->addDirectReceipients(array($rcpt));	// add direct receipients 
	 * $msg->addCCReceipientsarray($rcpt);			// add CC receipients
	 * $msg->addBCCReceipientsarray($rcpt);			// add BCC receipients 
	 * 
	 * // remove a single receipient by email and type
	 * 
	 * $msg->dropReceipient($email);		//drop a direct receipient
	 * $msg->dropCCReceipient($email);		//drop a CC receipient
	 * $msg->dropBCCReceipient($email);		//drop a BCC receipient
	 * 
	 * // clear a set of receipients
	 * 
	 * $msg->clearReceipients();			// clear direct receipients
	 * $msg->clearCCReceipients();			// clear CC receipients
	 * $msg->clearBCCReceipients();			// clear BCC receipients
	 * $msg->clearAllReceipients();			// clear all receipients
	 * 
	 * // get receipients
	 * $msg->getReceipients();					// get direct receipients
	 * $msg->getCCReceipients();				// get CC receipients
	 * $msg->getBCCReceipients();				// get BCC receipients
	 * $msg->getAllReceipients();				// get all receipients
	 * </code>
	 * @param string $prop function name
	 * @param mixed $val arguments
	 * @return mixed
	 */
	function __call($prop, $val)
	{
		switch ($prop) {
			case 'addDirectReceipient':
				return call_user_func_array(array($this,'addReceipient'), array($val[0],self::RCPT_DIRECT));
				break;
			case 'addReceipient':
				return call_user_func_array(array($this,'addReceipient'), array($val[0],self::RCPT_DIRECT));
				break;
			case 'addCCReceipient':
				return call_user_func_array(array($this,'addReceipient'), array($val[0],self::RCPT_CC));
				break;
			case 'addBCCReceipient':
				return call_user_func_array(array($this,'addReceipient'), array($val[0],self::RCPT_BCC));
				break;

			// Bulk
			case 'addDirectReceipients':
				return call_user_func_array(array($this,'addReceipients'), array($val[0],self::RCPT_DIRECT));
				break;
			case 'addReceipients':
				return call_user_func_array(array($this,'addReceipients'), array($val[0],self::RCPT_DIRECT));
				break;
			case 'addCCReceipients':
				return call_user_func_array(array($this,'addReceipients'), array($val[0],self::RCPT_CC));
				break;
			case 'addBCCReceipients':
				return call_user_func_array(array($this,'addReceipients'), array($val[0],self::RCPT_BCC));
				break;

			case 'dropReceipient':
				return call_user_func_array(array($this,'clearReceipient'), array($val[0],self::RCPT_DIRECT));
				break;
			case 'dropCCReceipient':
				return call_user_func_array(array($this,'clearReceipient'), array($val[0],self::RCPT_CC));
				break;
			case 'dropBCCReceipient':
				return call_user_func_array(array($this,'clearReceipient'), array($val[0],self::RCPT_BCC));
				break;
				// CLEAR BULK
			case 'clearReceipients':
				return call_user_func_array(array($this,'clearReceipients'), array(self::RCPT_DIRECT));
				break;
			case 'clearCCReceipients':
				return call_user_func_array(array($this,'clearReceipients'), array(self::RCPT_CC));
				break;
			case 'clearBCCReceipients':
				return call_user_func_array(array($this,'clearReceipients'), array(self::RCPT_BCC));
				break;
			case 'clearAllReceipients':
				return call_user_func_array(array($this,'clearReceipients'), array(self::RCPT_ALL));
				break;

			case 'getReceipients':
				return call_user_func_array(array($this,'getReceipients'), array(self::RCPT_All));
				break;
			case 'getDirectReceipients':
				return call_user_func_array(array($this,'getReceipients'), array(self::RCPT_DIRECT));
				break;
			case 'getCCReceipients':
				return call_user_func_array(array($this,'getReceipients'), array(self::RCPT_CC));
				break;
			case 'getBCCReceipients':
				return call_user_func_array(array($this,'getReceipients'), array(self::RCPT_BCC));
				break;
			case 'getAllReceipients':
				return call_user_func_array(array($this,'getReceipients'), array(self::RCPT_ALL));
				break;

				
			default:
				throw new \Exception(
				  'Sorry, AlianzMailer has no such method "'.$prop.'"');
				break;
		}
	}
}