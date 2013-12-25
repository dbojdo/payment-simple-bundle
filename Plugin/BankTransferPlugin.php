<?php

namespace Webit\Accounting\PaymentSimpleBundle\Plugin;

class BankTransferPlugin extends SimpleAbstractPlugin {
	/**
	 *
	 * @param string $paymentSystemName        	
	 * @return boolean
	 */
	public function processes($paymentSystemName) {
		return 'simple_bank_transfer' === $paymentSystemName;
	}
}
