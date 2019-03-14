<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Order;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TransactionController
 * @package App\Controller
 * @author Guillermo Quinteros <gu.quinteros@gmail.com>
 * @Route("/transactions")
 */
class TransactionController extends AbstractController
{
    /**
     * @var \Transbank\Webpay\WebpayOneClick
     */
    private $transaction;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * TransactionController constructor.
     * @param LoggerInterface $logger
     * @param TransactionService $transactionService
     * @param Filesystem $filesystem
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LoggerInterface $logger, TransactionService $transactionService, Filesystem $filesystem, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->transaction = $transactionService->getTestTransaction();
        $this->fileSystem = $filesystem;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @Route("/checkout", name="transaction_checkout")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function checkout(Request $request)
    {
        $amount = $request->query->get('amount');
        $buyOrder = $request->query->get('order');

        if (!$amount || !$buyOrder) {
            throw new BadRequestHttpException('Missing required params');
        }

        $this->log("Checkout | Amount : $amount | Order : $buyOrder");

        $this->checkOrderOrThrowException($buyOrder);

        $sessionId = uniqid();
        $appUrl = $this->getParameter('app_url');
        $returnUrl = "${appUrl}/transactions/result";
        $finalUrl = "${appUrl}/transactions/end";

        $this->log("Transaction Request | Amount : $amount | Order : $buyOrder | Session : $sessionId");

        try {
            $initResult = $this->transaction->initTransaction($amount, $buyOrder, $sessionId, $returnUrl, $finalUrl);
            $this->log("Transaction Status | ". json_encode($initResult));
            $this->log("Transaction Response | Token : $initResult->token | URL : $initResult->url");

            return $this->render('form.html.twig', [
                'url' => $initResult->url,
                'token' => $initResult->token
            ]);
        } catch (\Exception $e) {
            $this->log("Payment Rejected | Order : $buyOrder | Exception : ".$e->getMessage());
            return $this->render('rejection.html.twig');
        }
    }

    /**
     * @param Request $request
     * @Route("/result", name="transaction_result")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function result(Request $request)
    {
        $token = $request->get('token_ws');

        if (!$token) {
            throw new BadRequestHttpException('Missing token');
        }

        try {
            $this->log("Transbank Response | Token WS : $token");
            $webpayResponse = $this->transaction->getTransactionResult($token);
            $this->log("Transaction Result | ". json_encode($webpayResponse));
            $output = $webpayResponse->detailOutput;

            if ($output->responseCode !== 0) {
                throw new BadRequestHttpException('Payment declined');
            }

            $this->createOrderByWebPayResponse($webpayResponse);

            return $this->render('form.html.twig', [
                'url' => $webpayResponse->urlRedirection,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            $this->log("Payment Rejected | Exception : ".$e->getMessage());
            return $this->render('rejection.html.twig');
        }
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

    private function checkOrderOrThrowException($buyOrder)
    {
        $order = $this->entityManager
            ->getRepository(Order::class)
            ->findBy(['code' => $buyOrder, 'status' => Order::PAID]);

        if ($order) {
            throw new BadRequestHttpException('the order was already paid');
        }

        return;
    }

    /**
     * @param $details
     * @throws \Exception
     */
    private function log($details)
    {
        $log = new Log();
        $log->setDetails($details);
        $this->entityManager->persist($log);
        $this->entityManager->flush();
        return;
    }

    /**
     * @param $webpayResponse
     * @throws \Exception
     */
    private function createOrderByWebPayResponse($webpayResponse)
    {
        $order = new Order();
        $order->setAmount($webpayResponse['detailOutput']['amount']);
        $order->setCode($webpayResponse['buyOrder']);
        $order->setStatus(Order::PAID);
        $order->setWebpayResponse(json_encode($webpayResponse, JSON_PRETTY_PRINT));
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        return;
    }
}
