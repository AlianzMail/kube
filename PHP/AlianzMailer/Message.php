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


/**
 * Creates a new message 
 * html version is rerequired
 * alternate html version is also required
 * @package AlianzMailer
 */
class Message
{
	/**
	 * html body
	 * @var mixed
	 */
	public $body = '';
	/**
	 * alternate html body
	 * @var mixed
	 */
	public $alt_body = '';

	/**
	 * Create a new message object
	 * @param mixed $html html message body
	 * @param mixed $alt alternate html message
	 * @return Message
	 */
	function __construct($html = '', $alt = '')
	{
		$this->body = $html;
		$this->alt_body = $alt;
	}
	/**
	 * set html message body
	 * @param mixed $html html message
	 * @return Message
	 */
	public function setBody($html)
	{
		$this->body = $html;
		return $this;
	}
	/**
	 * return html message body
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * set alternate html message
	 * @param mixed $alt alternate message
	 * @return Message
	 */
	public function setAlt($alt)
	{
		$this->alt_body = $alt;
		return $this;
	}

	/**
	 * return alternate message body
	 * @return mixed
	 */
	public function getAlt()
	{
		return $this->alt_body;
	}
}