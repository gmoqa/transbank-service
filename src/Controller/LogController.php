<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class LogController
 * @package App\Controller
 * @author Guillermo Quinteros <gu.quinteros@gmail.com>
 * @Route("/logs")
 */
class LogController extends AbstractController
{
    /**
     * @Route("", name="logs_list")
     */
    public function index()
    {
        return $this->json([
            'message' => 'logs',
        ]);
    }
}
