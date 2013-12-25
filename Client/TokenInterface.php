<?php
namespace Webit\Accounting\PaymentSimpleBundle\Client;

interface TokenInterface {
	/**
	 * @return string
	 */
	public function getAuthId();
	
	/**
	 * @return string
	 */
	public function getPrivateKey();
}
