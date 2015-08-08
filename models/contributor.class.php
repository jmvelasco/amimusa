<?php 
namespace Models;

class Contributor 
{
	private $name;
	private $username;
	private $email;
	private $description;
	private $linkProfile;

	/**
	 * 
	 * @param string $username
	 * @param string $email
	 */
	public function __construct($username = '', $email = '')
	{
		$this->username = $username;
		$this->email = $email;
		
	}
	
	/*
	 * Get Methods
	 */
	public function getName()
	{
		return isset($this->name)?$this->name:'';
	}
	
	public function getUsername()
	{
		return isset($this->username)?$this->username:'';
	}
	
	public function getEmail()
	{
		return isset($this->email)?$this->email:'';
	}
	
	public function getDescription()
	{
		return isset($this->description)?$this->description:'';
	}
	
	public function getLinkProfile()
	{
		return isset($this->linkProfile)?$this->linkProfile:'';
	}
	
	/*
	 * Set Methods
	 */
	public function setName($name = '')
	{
		$this->name = $name;
	}
	
	public function setUsername($username = '')
	{
		$this->username = $username;
	}
	
	public function setEmail($email = '')
	{
		$this->email = $email;
	}
	
	public function setDescription($description = '')
	{
		$this->description = $description;
	}
	
	public function setLinkProfile($linkProfile = '')
	{
		$this->linkProfile = $linkProfile;
	}
}
?>