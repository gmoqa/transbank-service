<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 * @author Guillermo Quinteros <gu.quinteros@gmail.com>
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("", name="home")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to TBK API',
            'checkout' => $this->getParameter('app_url').'/transactions/checkout?amount='.rand(1000, 10000).'&order='.uniqid(),
            'cards' => [
                'VISA' => [
                    'NUMBER' => '4051885600446623',
                    'CVV' => '123',
                    'DETAIL' => 'Cualquier fecha de expiración. Esta tarjeta genera transacciones aprobadas.'
                ],
                'MASTERCARD' => [
                    'NUMBER' => '5186059559590568',
                    'CVV' => '123',
                    'DETAIL' => 'Cualquier fecha de expiración. Esta tarjeta genera transacciones rechazadas.'
                ],
                'REDCOMPRA' => [
                    'NUMBER' => '4051885600446623',
                    'CVV' => '123',
                    'DETAIL' => 'Genera transacciones aprobadas (para operaciones que permiten débito Redcompra)'
                ],
                'REDCOMPRA 2' => [
                    'NUMBER' => '5186059559590568',
                    'CVV' => '123',
                    'DETAIL' => 'Genera transacciones rechazadas (para operaciones que permiten débito Redcompra)'
                ]
            ]
        ]);
    }
}
