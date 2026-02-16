<?php

namespace App\Controller;

use App\Entity\Tournoi;
use App\Repository\TournoiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TournoiController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/api/tournois', methods: 'GET', name: 'app_tournoi_index')]
    public function index(TournoiRepository $tournoiRepository): JsonResponse
    {
        $tournois = $tournoiRepository->findAll();

        $data = array_map(function (Tournoi $tournoi) {
            return [
                'id' => $tournoi->getId(),
                'date' => $tournoi->getDate(),
                'niveau' => $tournoi->getLevel(),
                'adresse' => $tournoi->getAddress(),
                'status' => $tournoi->getStatus(),
                'participations' => $tournoi->getParticipations(),
                'Nom du tournois' => $tournoi->getTournamentName()
            ];
        }, $tournois);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
