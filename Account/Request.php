<?php
namespace Webit\Accounting\PaymentSimpleBundle\Account;

class Request implements RequestInterface {
	/**
	 * 
	 * @var string
	 */
	protected $authId;
	
	/**
	 * 
	 * @var string
	 */
	protected $paymentInstructionId;
	
	/**
	 *
	 * @var string
	 */
	protected $status;
	
	/**
	 * 
	 * @var string
	 */
	protected $symbol;
	
	/**
	 *
	 * @var string
	 */
	protected $amount;
	
	/**
	 *
	 * @var string
	 */
	protected $sign;
	
	public function __construct($authId = null, $paymentInstructionId = null, $status = null, $symbol = null, $amount = null, $sign = null) {
		if($authId) {
			$this->setAuthId($authId);
		}
		
		if($paymentInstructionId) {
			$this->setPaymentInstructionId($paymentInstructionId);
		}
		
		if($status) {
			$this->setStatus($status);
		}
		
		if($symbol) {
			$this->setSymbol($symbol);
		}
		
		if($amount) {
			$this->setAmount($amount);
		}
		
		if($sign) {
			$this->setSign($sign);
		}
	}
	
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
	public function getPaymentInstructionId() {
		return $this->paymentInstructionId;
	}
	
	/**
	 *
	 * @param string $paymentInstructionId
	 */
	public function setPaymentInstructionId($paymentInstructionId) {
		$this->paymentInstructionId = $paymentInstructionId;
	}

	/**
	 *
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 *
	 * @param string $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getSymbol() {
		return $this->symbol;
	}
	
	/**
	 * 
	 * @param string $symbol
	 */
	public function setSymbol($symbol) {
		$this->symbol = $symbol;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getAmount() {
		return $this->amount;
	}
	
	/**
	 *
	 * @param string $amount
	 */
	public function setAmount($amount) {
		$this->amount = str_replace(',', '.', (string)$amount);
	}
	
	/**
	 *
	 * @return string
	 */
	public function getSign() {
		return $this->sign;
	}
	
	/**
	 * 
	 * @param string $sign
	 * @throws \RuntimeException
	 */
	public function setSign($sign) {
		if($this->sign != null) {
			throw new \RuntimeException('This request has been already signed.');
		}
		
		$this->sign = $sign;
	}
}
