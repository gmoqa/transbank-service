<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Transbank\Webpay\Configuration;
use Transbank\Webpay\Webpay;

/**
 * Class TransactionController
 * @package App\Controller
 * @author Guillermo Quinteros <gu.quinteros@gmail.com>
 * @Route("/transactions")
 */
class TransactionController extends AbstractController
{
    /**
     * @param Request $request
     * @Route("/checkout", name="transaction_checkout")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkout(Request $request)
    {
        $amount = $request->query->get('amount');
        $order = $request->query->get('order');

        if (!$amount || !$order) {
            throw new BadRequestHttpException('Missing required params');
        }

        $sessionId = uniqid();

        $returnUrl = "http://localhost/transactions/result";
        $finalUrl = "http://localhost/transactions/end";

        $transaction = (new Webpay(Configuration::forTestingWebpayPlusNormal()))->getNormalTransaction();

        $initResult = $transaction->initTransaction($amount, $order, $sessionId, $returnUrl, $finalUrl);

        return $this->render('form.html.twig', [
            'url' => $initResult->url,
            'token' => $initResult->token
        ]);
    }

    /**
     * @Route("/result", name="transaction_result")
     */
    public function result() {

    }

    /**
     * @Route("/end", name="transaction_end")
     */
    public function end() {

    }
}
