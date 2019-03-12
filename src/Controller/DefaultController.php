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
        ]);
    }
}
