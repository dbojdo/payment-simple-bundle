<?php

namespace Webit\Accounting\PaymentSimpleBundle\Plugin;

class CashPlugin extends SimpleAbstractPlugin {
	/**
	 *
	 * @param string $paymentSystemName        	
	 * @return boolean
	 */
	public function processes($paymentSystemName) {
		return 'simple_cash' === $paymentSystemName;
	}
}
