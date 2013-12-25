<?php
namespace Webit\Accounting\PaymentSimpleBundle\Account;

use Webit\Accounting\PaymentSimpleBundle\Client\TokenInterface;

interface SignProviderInterface {
	/**
	 * 
	 * @param RequestInterface $request
	 * @param TokenInterface $token
	 * @return string
	 */
	public function createRequestSign(RequestInterface $request, TokenInterface $token);
	
	/**
	 * 
	 * @param RequestInterface $request
	 * @param TokenInterface $token
	 */
	public function signRequest(RequestInterface $request, TokenInterface $token);
	
	/**
	 * 
	 * @param RequestInterface $request
	 * @param TokenInterface $token
	 * @return bool
	 */
	public function isRequestValid(RequestInterface $request, TokenInterface $token);
}
