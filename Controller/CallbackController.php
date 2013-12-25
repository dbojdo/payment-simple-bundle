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

class CallbackController extends Controller
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
	private $pc;

	public function __construct(TokenProviderInterface $tokenProvider, SignProviderInterface $signProvider, PluginControllerInterface $pc) {
		$this->tokenProvider = $tokenProvider;
		$this->signProvider = $signProvider;
		$this->pc = $pc;
	}
	
	public function receiveNotificationAction() {
		$request = $this->getInternalRequest();
		
		$pi = $this->pc->getPaymentInstruction($request->getPaymentInstructionId());
		$token = $this->tokenProvider->getToken($authId);
		$validSign = $this->signProvider->createRequestSign($request, $token);
		
		$logger = $this->get('logger');
		 
		if ($request->getSign() !== $validSign) {
			$logger->err('[Payment Simple - URLC] pin verification failed');
		
			return new Response('FAIL', 500);
		}
		
		if (null === $transaction = $pi->getPendingTransaction()) {
			$logger->err('[Payment Simple - URLC] no pending transaction found for the payment instruction');
		
			return new Response('FAIL', 500);
		}
		
		$transaction->setReferenceNumber($this->getRequest()->get('orderid'));
		$amount = (float)$request->getAmount();
		
		$request = $this->getRequest();
		$transaction->getExtendedData()->set('status', $request->getStatus());
		$transaction->getExtendedData()->set('amount', $amount);
		
		try {
			$this->pc->approveAndDeposit($transaction->getPayment()->getId(), $amount);
		} catch (\Exception $e) {
			$logger->err(sprintf('[Payment Simple - URLC] %s', $e->getMessage()));
		
			return new Response('FAIL', 500);
		}
		
		$this->get('doctrine.orm.entity_manager')->flush();
		
		$logger->info(sprintf('[PaymentSimple - URLC] Payment instruction %s successfully updated', $pi->getId()));
		
		return new Response('OK');
	}
	
	/**
	 * 
	 * @return \Webit\Accounting\PaymentSimpleBundle\Account\Request
	 */
	private function getInternalRequest() {
		$request = new Request(
			$this->getRequest()->get('authId'),
			$this->getRequest()->get('piid'),
			$this->getRequest()->get('status'),
			$this->getRequest()->get('symbol'),
			$this->getRequest()->get('amount'),
			$this->getRequest()->get('sign')
		);
		
		return $request;
	}
}
