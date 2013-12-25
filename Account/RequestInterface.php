<?php
namespace Webit\Accounting\PaymentSimpleBundle\Account;

interface RequestInterface {
	/**
	 *
	 * @return string
	 */
	public function getAuthId();
	
	/**
	 *
	 * @param string $authId
	 */
	public function setAuthId($authId);
	
	/**
	 * 
	 * @return string
	 */
	public function getPaymentInstructionId();
	
	/**
	 * 
	 * @param string $paymentInstructionId
	 */
	public function setPaymentInstructionId($paymentInstructionId);
	
	/**
	 *
	 * @return string
	 */
	public function getStatus();
	
	/**
	 * 
	 * @param string $status
	 */
	public function setStatus($status);
	
	/**
	 * @return string
	 */
	public function getSymbol();
	
	/**
	 * 
	 * @param string $symbol
	 */
	public function setSymbol($symbol);
	
	/**
	 *
	 * @return string
	 */
	public function getAmount();
	
	/**
	 * 
	 * @param string $amount
	 */
	public function setAmount($amount);
	
	/**
	 *
	 * @return string
	 */
	public function getSign();
	
	/**
	 *
	 * @param string $sign
	 */
	public function setSign($sign);
}
