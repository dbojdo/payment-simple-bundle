<?php

namespace Webit\Accounting\PaymentSimpleBundle\Plugin;

class CodPlugin extends SimpleAbstractPlugin {
	/**
	 *
	 * @param string $paymentSystemName        	
	 * @return boolean
	 */
	public function processes($paymentSystemName) {
		return 'simple_cod' === $paymentSystemName;
	}
}