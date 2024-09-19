<?php

namespace App\Controller;

use App\Repository\NoteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(Request $request, NoteRepository $nr, PaginatorInterface $paginator): Response
    {
        $searchQuery = $request->query->get('q'); // Récupère la query fournie par l'utilisateur
        if (!$searchQuery) {
            return $this->render('search/results.html.twig') ; // Si aucune query n'est fournie, on redirige sans paramètres
        }
 
        $pagination = $paginator->paginate(
            $nr->findByQuery($searchQuery), /* Requête pour récupérer les notes qui contiennent la query */
            $request->query->getInt('page', 1), /* Numéro de page */
            10 /* Nombre de notes par page */
        );
        return $this->render('search/results.html.twig', [
            'allNotes' => $pagination,
            'searchQuery' => $searchQuery
        ]);
    }
}
