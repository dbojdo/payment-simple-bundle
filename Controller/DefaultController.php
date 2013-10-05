<?php

namespace Webit\Accounting\PaymentSimpleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WebitAccountingPaymentSimpleBundle:Default:index.html.twig', array('name' => $name));
    }
}
