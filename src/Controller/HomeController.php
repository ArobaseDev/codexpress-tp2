<?php

namespace App\Controller;

use App\Repository\NoteRepository;
use App\Service\EmailNotificationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(NoteRepository $nr): Response
    {
        $lastNotes = $nr->findBy(
            ['is_public' => true], // Filtre les notes publiques
            ['created_at' => 'DESC'], // Trie les notes par date de création
            6 // Limite à 6 notes
        );
        return $this->render('home/index.html.twig', [
            'lastNotes' => $lastNotes, // Envoie des notes à la vue Twig
            'totalNotes' => count($nr->findAll()) // Renvoi le compte total de notes
        ]);
    }

    #[Route('/email', name: 'app_email')]
    public function testEmail(Request $request, EmailNotificationService $ems): Response
    {
        $case = $request->query->get('case');
        if ($case) {
        //    $ems->sendEmail($this->getUser()->getEmail(), $case);
            $ems->sendEmail($this->getUser()->getEmail(), $case);
        }
        return new Response("
            Email sent to {$this->getUser()->getEmail()} <br>
            Choose a case: <br>
            <a href='/email?case=premium'>Premium</a> <br>
            <a href='/email?case=registration'>Registration</a>
        ");
    }
}
