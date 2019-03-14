<?php

namespace App\Controller;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * LogController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("", name="logs_list")
     */
    public function index()
    {
        $logs = $this->entityManager->getRepository(Log::class)->findBy([], ['id' => 'desc'], 100);

        return $this->render('log/index.html.twig', [
           'logs' => $logs
        ]);
    }

    /**
     * @throws \Exception
     * @Route("/test", name="logs_persist_logs")
     */
    public function save()
    {
        $log = new Log();
        $log->setDetails('Test Log');
        $this->entityManager->persist($log);
        $this->entityManager->flush();
        return $this->json([
            'message' => 'Done',
        ]);
    }
}
