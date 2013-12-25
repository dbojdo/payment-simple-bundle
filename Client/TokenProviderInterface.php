<?php
namespace Webit\Accounting\PaymentSimpleBundle\Client;

interface TokenProviderInterface {
	/**
	 * @param $authId
	 * @return TokenInterface
	 */
	public function getToken($authId);
	
	/**
	 * 
	 * @param TokenInterface $token
	 */
	public function registerToken(TokenInterface $token);
}
