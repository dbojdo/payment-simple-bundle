<?php
namespace Webit\Accounting\PaymentSimpleBundle\Account;

use Webit\Accounting\PaymentSimpleBundle\Client\TokenInterface;

class SignProvider implements SignProviderInterface {
	/**
	 * 
	 * @param RequestInterface $request
	 * @param TokenInterface $token
	 * @return string
	 */
	public function createRequestSign(RequestInterface $request, TokenInterface $token) {
		$str = implode('',array(
			$request->getPaymentInstructionId(), 
			$request->getStatus(), 
			$request->getAmount(),
			$token->getPrivateKey()
		));
		
		$sign = md5($str);
		
		return $sign;
	}
	
	/**
	 * 
	 * @param RequestInterface $request
	 * @param TokenInterface $token
	 */
	public function signRequest(RequestInterface $request, TokenInterface $token) {
		if($request->getSign() === null) {
			$sign = $this->createRequestSign($request, $token);
			$request->setSign($sign);
		}
	}
	
	/**
	 * 
	 * @param RequestInterface $request
	 * @param TokenInterface $token
	 * @return bool
	 */
	public function isRequestValid(RequestInterface $request, TokenInterface $token) {
		$validSign = $this->createRequestSign($request, $token);
		$isValid = $validSign === $request->getSign();
		
		return $isValid;
	}
}
