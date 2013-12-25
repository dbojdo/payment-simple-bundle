<?php

namespace Webit\Accounting\PaymentSimpleBundle\Plugin;

use JMS\Payment\CoreBundle\Model\ExtendedDataInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\TimeoutException;
use JMS\Payment\CoreBundle\Plugin\ErrorBuilder;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\BlockedException;
use JMS\Payment\CoreBundle\Entity\ExtendedData;

use Symfony\Component\Routing\RouterInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException as PluginFinancialException;

abstract class SimpleAbstractPlugin extends AbstractPlugin
{
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ERROR = 'error';
    
    public static $statuses = array(
        self::STATUS_ACCEPTED    => 'Accepted',
        self::STATUS_REJECTED    => 'Rejected',
    	self::STATUS_ERROR    	 => 'Error'
    );

    /**
     * @var Router
     */
    protected $router;
   
    /**
     * 
     * @param Router $router
     * @param SignCalculatorInterface $signCalculator
     * @param string $url
     * @param bool $testMode
     */
    public function __construct(RouterInterface $router, TokenInterface $token, SignCalculatorInterface $signCalculator, $url, $testMode)
    {
        $this->router = $router;
        $this->token = $token;
        $this->signCalculator = $signCalculator;
        $this->url = $url;
        $this->testMode = $testMode;
    }

    public function setBuzz(Browser $buzz) {
        $this->buzz = $buzz;
    }
    
    /**
     * This method executes a deposit transaction without prior approval
     * (aka "sale", or "authorization with capture" transaction).
     *
     * A typical use case for this method is an electronic check payments
     * where authorization is not supported. It can also be used to deposit
     * money in only one transaction, and thus saving processing fees for
     * another transaction.
     *
     * @param FinancialTransactionInterface $transaction The transaction
     * @param boolean                       $retry       Retry
     */
    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {        
        $this->approve($transaction, $retry);
        $this->deposit($transaction, $retry);
    }
    
    /**
     * This method executes an approve transaction.
     *
     * By an approval, funds are reserved but no actual money is transferred. A
     * subsequent deposit transaction must be performed to actually transfer the
     * money.
     *
     * A typical use case, would be Credit Card payments where funds are first
     * authorized.
     *
     * @param FinancialTransactionInterface $transaction The transaction
     * @param boolean                       $retry       Retry
     */
    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        $data = $transaction->getExtendedData();
    	$this->checkExtendedDataBeforeApproveAndDeposit($data);

        switch ($data->get('status')) {
            case self::STATUS_ACCEPTED:
                break;
            case self::STATUS_REJECTED:
            case self::STATUS_ERROR:
                $ex = new PluginFinancialException('Payment status error: '.$data->get('status'));
	            $ex->setFinancialTransaction($transaction);
	            $transaction->setResponseCode('error');
	            $transaction->setReasonCode(PluginInterface::REASON_CODE_INVALID);
	            
	            throw $ex;
        }
    }

    /**
     * This method executes a deposit transaction (aka capture transaction).
     *
     * This method requires that the Payment has already been approved in
     * a prior transaction.
     *
     * A typical use case are Credit Card payments.
     *
     * @param FinancialTransactionInterface $transaction The transaction
     * @param boolean                       $retry       Retry
     *
     * @return mixed
     */
    public function deposit(FinancialTransactionInterface $transaction, $retry)
    {
        $data = $transaction->getExtendedData();
        $this->checkExtendedDataBeforeApproveAndDeposit($data);

        switch ($data->get('status')) {
            case self::STATUS_ACCEPTED:
                break;
            case self::STATUS_REJECTED:
            case self::STATUS_ERROR:
                $ex = new PluginFinancialException('Payment status error: '.$data->get('status'));
	            $ex->setFinancialTransaction($transaction);
	            $transaction->setResponseCode('error');
	            $transaction->setReasonCode(PluginInterface::REASON_CODE_INVALID);
	            
	            throw $ex;
        }

        $transaction->setProcessedAmount($data->get('amount'));
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    /**
     * @param PaymentInstructionInterface $paymentInstruction
     *
     * @throws \JMS\Payment\CoreBundle\Plugin\Exception\InvalidPaymentInstructionException
     */
    public function checkPaymentInstruction(PaymentInstructionInterface $paymentInstruction)
    {
        $errorBuilder = new ErrorBuilder();
        $data = $paymentInstruction->getExtendedData();

        // TODO Check requirements here
        if ($errorBuilder->hasErrors()) {
            throw $errorBuilder->getException();
        }
    }

    /**
     * @param string $paymentSystemName
     *
     * @return boolean
     */
    abstract public function processes($paymentSystemName);
}