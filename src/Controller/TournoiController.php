<?php

namespace App\Controller;

use App\Entity\Tournoi;
use App\Repository\TournoiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[IsGranted('ROLE_USER')]
    #[Route('/api/tournois/{id}', methods: 'GET', name: 'app_tournoi_show')]
    public function show(Tournoi $tournoi): JsonResponse
    {
        $data = [
            'id' => $tournoi->getId(),
            'date' => $tournoi->getDate(),
            'niveau' => $tournoi->getLevel(),
            'adresse' => $tournoi->getAddress(),
            'status' => $tournoi->getStatus(),
            'participations' => $tournoi->getParticipations(),
            'Nom du tournois' => $tournoi->getTournamentName()
        ];
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/tournois', methods: 'POST', name: 'app_tournois_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $tournoi = new Tournoi;

        $tournoi->setTournamentName($data['tournamentName']);
        $tournoi->setAddress($data['adress']);
        $tournoi->setLevel($data['level']);
        $date = new \DateTimeImmutable($data['date']);
        $tournoi->setDate($date);
        $tournoi->setStatus($data['status']);

        $errors = $validator->validate($tournoi);

        if (count($errors) > 0) {
            $messages = [];

            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return new JsonResponse([
                'errors' => $messages
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->persist($tournoi);
        $em->flush();

        return new JsonResponse(['status' => 'Tournoi créé'], JsonResponse::HTTP_CREATED);
    }
}
