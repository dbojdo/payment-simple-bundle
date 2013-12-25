<?php

namespace Webit\Accounting\PaymentSimpleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Webit\Accounting\PaymentSimpleBundle\Client\TokenProviderInterface;
use Doctrine\ORM\EntityManager;
use JMS\Payment\CoreBundle\PluginController\PluginControllerInterface;
use Webit\Accounting\PaymentSimpleBundle\Account\Request;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use Webit\Accounting\PaymentSimpleBundle\Account\SignProviderInterface;
use Webit\Accounting\PaymentSimpleBundle\Client\TokenInterface;
use Webit\Accounting\PaymentSimpleBundle\Account\RequestInterface;

class AdminController extends Controller
{
	/**
	 * 
	 * @var TokenProviderInterface
	 */
	private $tokenProvider;
	
	/**
	 * 
	 * @var SignProviderInterface
	 */
	private $signProvider;
	
	/**
	 * 
	 * @var PluginControllerInterface
	 */
	private $pci;
	
	public function __construct(TokenProviderInterface $tokenProvider, SignProviderInterface $signProvider, PluginControllerInterface $pci) {
		$this->tokenProvider = $tokenProvider;
		$this->signProvider = $signProvider;
		$this->pci = $pci;
	}
	
	/**
	 * 
	 * @param string $authId
	 * @param string $paymentInstructionId
	 * @return \Webit\Accounting\PaymentSimpleBundle\Account\Request
	 */
	private function createRequest(TokenInterface $token, $paymentInstructionId, $status, $symbol) {
		$token = $this->tokenProvider->getToken($authId);
		$paymentInstruction = $this->pci->getPaymentInstruction($paymentInstructionId);
		$amount = $paymentInstruction->getAmount();
		
		// FIXME: create request with factory
		$request = new Request($token->getAuthId(), $paymentInstruction->getId(), $status, $amount);
		$this->signProvider->signRequest($request, $token);
		
		return $request;
	}
	
	public function acceptPaymentAction($authId, $paymentInstructionId) {
		$request = $this->createRequest($token, $paymentInstructionId, 'accepted');
		$response = $this->doRequest($request);
	}
	
	public function rejectPaymentAction($paymentInstructionId, $authId) {
		$request = $this->createRequest($token, $paymentInstructionId, 'rejected');
		$response = $this->doRequest($request);
	}
	
	private function doRequest(RequestInterface $request) {
		$response = $this->forward('webit_accounting_payment_simple:receiveNotifityAction', null, array('authId'=>$authId,'piid'=>$paymentInstructionId,'status'=>$request->getStatus(), 'sign'=>$request->getSign()));
		
		return $response;
	}
}
