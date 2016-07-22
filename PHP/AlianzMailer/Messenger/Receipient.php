<?php

/**
 * @package AlianzMailer
 * @author AlianzMail
 * @version 1.0.0
 * @copyright AlianzMail 2016 
 * @link http://alianzmail.com/docs
 * 
*/

namespace AlianzMailer\Messenger;


/**
 * Creates a new message receipient
 * Email and type are required
 * the receipient name is optional
 * @package AlianzMailer
 * {@inheritdoc}
 */
class Receipient 
{
	/**
	 * email address
	 * @var string
	 */
	public $email;
	/**
	 * name of receipient
	 * @var string
	 */
	public $name;
	/**
	 * type of receipient
	 * @var string
	 */
	public $type;

	/**
	 * Create new Receipient
	 * @param string $email required email address
	 * @param string $type receipient type
	 * @param string|null $name name of receipient
	 * @return Receipient
	 */
	function __construct( $email,$type, $name = null)
	{
		$this->email = $email;
		$this->name = $name;
		$this->type = $type;
	}
	/**
	 * Set email address
	 * @param string $email email address
	 * @return Receipient
	 */
	public function setEmail( $email)
	{
		$this->email = $email;
		return $this;
	}
	/**
	 * Get email address
	 * @return string
	 */
	public function getEmail( )
	{
		return $this->email;
	}
	/**
	 * set receipient name
	 * @param string $name name of receipient
	 * @return Receipient
	 */
	public function setName( $name)
	{
		$this->name = $name;
		return $this;
	}
	/**
	 * return name of receipient
	 * @return string
	 */
	public function getName( )
	{
		return $this->name;
	}
	/**
	 * Set type of receipient
	 * @param string $type type of receipient
	 * @see Messenger
	 * @return Receipient
	 */
	public function setType( $type)
	{
		$this->type = $type;
		return $this;
	}
	/**
	 * return type of receipient
	 * @return string
	 */
	public function getType( )
	{
		return $this->type;
	}
	/**
	 * reset receipient
	 * @return Receipient
	 */
	public function clear()
	{
		$this->name = null;
		$this->email = null;
		return $this;
	}

	/**
	 * Compile receipient info
	 * @return array
	 */
	public function compile()
	{
		$compiled = array(
			'email' => $this->email
			);
		if ($this->name) {
			$compiled['name'] = $this->name;
		}
		return $compiled;
	}
}
