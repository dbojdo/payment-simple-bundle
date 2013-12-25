<?php
namespace Webit\Accounting\PaymentSimpleBundle\Client;

class Token implements TokenInterface {
	/**
	 * 
	 * @var string
	 */
	protected $authId;
	
	/**
	 * 
	 * @var string
	 */
	protected $privateKey;
	
	public function __construct($authId, $privateKey) {
		$this->authId = $authId;
		$this->privateKey = $privateKey;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getAuthId() {
		return $this->authId;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getPrivateKey() {
		return $this->privateKey;
	}
}
