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
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\BlockedException;
use JMS\Payment\CoreBundle\Entity\ExtendedData;
use Webit\Accounting\PaymentCashbillBundle\RedirectFormParser\RedirectFormParserInterface;
use Webit\Accounting\PaymentSimpleBundle\Plugin\Action\DoCashPayment;
use Webit\Accounting\PaymentSimpleBundle\Plugin\Action\DoBankTransfer;
use Webit\Accounting\PaymentSimpleBundle\Plugin\Action\DoCodPayment;

class SimplePlugin extends AbstractPlugin
{
    const STATUS_OK = 'ok';
    const STATUS_ERR = 'err';

    public static $statuses = array(
        self::STATUS_OK    => 'Success',
        self::STATUS_ERR       => 'Error'
    );
    
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
        if ($transaction->getState() === FinancialTransactionInterface::STATE_NEW) {
            throw $this->createSimpleRedirectActionException($transaction);
        }
        
        $this->approve($transaction, $retry);
        $this->deposit($transaction, $retry);
    }

    /**
     * @param FinancialTransactionInterface $transaction
     *
     * @return ActionRequiredException
     */
    public function createSimpleRedirectActionException(FinancialTransactionInterface $transaction)
    {
        $actionRequest = new ActionRequiredException('Do payment.');
        $actionRequest->setFinancialTransaction($transaction);

        $instruction = $transaction->getPayment()->getPaymentInstruction();
        
        $psn = $instruction->getPaymentSystemName();
        switch($instruction->getPaymentSystemName()) {
        	case 'simple_cash':
        		$actionRequest->setAction(new DoCashPayment());
        	break;
        	case 'simple_bank_transfer':
        		$actionRequest->setAction(new DoBankTransfer());
        	break;
        	case 'simple_cod':
        		$actionRequest->setAction(new DoCodPayment());
        	break;
        	default:
        		throw new \Exception('Unsupported payment system: ' .$instruction->getPaymentSystemName());
        }
        
        return $actionRequest;
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
        $this->checkExtendedDataBeforeApproveAndDeposit($transaction);
        
        if($data->get('status') != self::STATUS_OK) {
            $ex = new FinancialException('Payment status error: '.$data->get('status'));
            $ex->setFinancialTransaction($transaction);
            $transaction->setResponseCode('Error');
            
            throw $ex;
        }
        
        $transaction->setReferenceNumber($data->get('deposit_symbol'));
        $transaction->setProcessedAmount($data->get('amount'));
        
        $date = $data->get('deposit_date');
        if($date) {
        	$date = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        	$this->forceUpdateDate($transaction, $date);
        }
        
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
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

        $this->checkExtendedDataBeforeApproveAndDeposit($transaction);

        switch ($data->get('status')) {
            case self::STATUS_OK:
                break;
            case self::STATUS_ERR:
                $ex = new FinancialException('PaymentAction rejected.');
                $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
                $transaction->setReasonCode(PluginInterface::REASON_CODE_BLOCKED);
                $ex->setFinancialTransaction($transaction);

                throw $ex;
        }

        $transaction->setReferenceNumber($data->get('deposit_symbol'));
        $transaction->setProcessedAmount($data->get('amount'));
        
        $date = $data->get('deposit_date');
        if($date) {
        	$date = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        	$this->forceUpdateDate($transaction, $date);
        }
        
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    private function forceUpdateDate(FinancialTransactionInterface $transaction, \DateTime $date) {
    	$ro = new \ReflectionObject($transaction);
    	$p = $ro->getProperty('createdAt');
    	$p->setAccessible(true);
    	$p->setValue($transaction, $date);
    }
    
    /**
     * Check that the extended data contains the needed values
     * before approving and depositing the transation
     *
     * @param FinancialTransactionInterface $transaction
     *
     * @throws BlockedException
     */
    protected function checkExtendedDataBeforeApproveAndDeposit(FinancialTransactionInterface $transaction) {
		$data = $transaction->getExtendedData();
        if (!$data->has('status') || !$data->has('deposit_symbol') || !$data->has('amount')) {
            throw $this->createSimpleRedirectActionException($transaction);
        }
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
    public function processes($paymentSystemName)
    {
        return in_array($paymentSystemName, array('simple_cash','simple_bank_transfer','simple_cod'));
    }
}