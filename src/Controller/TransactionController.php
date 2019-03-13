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
        $buyOrder = $request->query->get('order');

        if (!$amount || !$buyOrder) {
            throw new BadRequestHttpException('Missing required params');
        }

        $sessionId = uniqid();

        $appUrl = $this->getParameter('app_url');

        $returnUrl = "${appUrl}/transactions/result";
        $finalUrl = "${appUrl}/transactions/end";

        $transaction = (new Webpay(Configuration::forTestingWebpayPlusNormal()))->getNormalTransaction();

        $initResult = $transaction->initTransaction($amount, $buyOrder, $sessionId, $returnUrl, $finalUrl);

        return $this->render('form.html.twig', [
            'url' => $initResult->url,
            'token' => $initResult->token
        ]);
    }

    /**
     * @param Request $request
     * @Route("/result", name="transaction_result")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function result(Request $request)
    {
        $token = $request->get('token_ws');

        if (!$token) {
            throw new BadRequestHttpException('Missing token');
        }

        $transaction = (new Webpay(Configuration::forTestingWebpayPlusNormal()))->getNormalTransaction();
        $webpayResponse = $transaction->getTransactionResult($token);
        $output = $webpayResponse->detailOutput;

        if ($output->responseCode !== 0) {
            throw new BadRequestHttpException('Payment declined');
        }

        return $this->render('form.html.twig', [
            'url' => $webpayResponse->urlRedirection,
            'token' => $token
        ]);
    }

    /**
     * @param Request $request
     * @Route("/end", name="transaction_end")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function end(Request $request)
    {
        $token = $request->get('token_ws');

        if (!$token) {
            throw new BadRequestHttpException('Missing token');
        }

        return $this->render('success.html.twig');
    }
}
