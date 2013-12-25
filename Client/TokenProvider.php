<?php
namespace Webit\Accounting\PaymentSimpleBundle\Client;

class TokenProvider implements TokenProviderInterface {
	/**
	 * 
	 * @var array
	 */
	protected $tokens = array();
	
	/**
	 * @param $authId
	 * @return TokenInterface
	 */
	public function getToken($authId) {
		if(isset($this->tokens[$authId])) {
			return $this->tokens[$authId];
		}
		
		return null;
	}
	
	/**
	 * 
	 * @param TokenInterface $token
	 */
	public function registerToken(TokenInterface $token) {
		$this->tokens[$token->getAuthId()] = $token;
	}
}
