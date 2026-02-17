<?php

namespace App\Controller;

use App\Entity\Tournoi;
use App\Entity\Participation;
use App\Repository\MembreRepository;
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
#[Route('/api/tournois', methods: ['GET'], name: 'app_tournoi_index')]
public function index(TournoiRepository $tournoiRepository): JsonResponse
{
    $tournois = $tournoiRepository->findAll();

    $data = array_map(function (Tournoi $tournoi) {

        $participations = array_map(function ($participation) {
            return [
                'id' => $participation->getId(),
                'status' => $participation->getStatus(),
                'joinedAt' => $participation->getJoinedAt()?->format('Y-m-d H:i:s'),
                'membre' => [
                    'id' => $participation->getMember()->getId(),
                    'nom' => $participation->getMember()->getFullName(),
                    'email' => $participation->getMember()->getEmail(),
                ]
            ];
        }, $tournoi->getParticipations()->toArray());

        return [
            'id' => $tournoi->getId(),
            'date' => $tournoi->getDate()?->format('Y-m-d'),
            'niveau' => $tournoi->getLevel(),
            'adresse' => $tournoi->getAddress(),
            'status' => $tournoi->getStatus(),
            'nom du tournoi' => $tournoi->getTournamentName(),
            'participations' => $participations
        ];
    }, $tournois);

    return new JsonResponse($data, JsonResponse::HTTP_OK);
}

    #[IsGranted('ROLE_USER')]
    #[Route('/api/tournois/{id}', methods: ['GET'], name: 'app_tournoi_show')]
    public function show(Tournoi $tournoi): JsonResponse
    {
        $participations = array_map(function ($participation) {
            return [
                'id' => $participation->getId(),
                'status' => $participation->getStatus(),
                'joinedAt' => $participation->getJoinedAt()?->format('Y-m-d H:i:s'),
                'membre' => [
                    'id' => $participation->getMember()->getId(),
                    'nom' => $participation->getMember()->getFullName(),
                    'email' => $participation->getMember()->getEmail(),
                ]
            ];
        }, $tournoi->getParticipations()->toArray());

        $data = [
            'id' => $tournoi->getId(),
            'date' => $tournoi->getDate()?->format('Y-m-d'),
            'niveau' => $tournoi->getLevel(),
            'adresse' => $tournoi->getAddress(),
            'status' => $tournoi->getStatus(),
            'nom' => $tournoi->getTournamentName(),
            'participations' => $participations
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

    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/api/tournois/{tournoiId}/membres/{membreId}',
        methods: 'POST',
        name: 'app_tournoi_addMember'
    )]
    public function addMember(
        int $tournoiId,
        int $membreId,
        TournoiRepository $tournoiRepository,
        MembreRepository $membreRepository,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $tournoi = $tournoiRepository->find($tournoiId);
        $membre = $membreRepository->find($membreId);

        if (!$tournoi) {
            return new JsonResponse(['error' => 'Groupe introuvable'], 404);
        }

        if (!$membre) {
            return new JsonResponse(['error' => 'Membre introuvable'], 404);
        }

        foreach ($tournoi->getParticipations() as $participation) {
            if ($participation->getMember()->getId() === $membre->getId()) {
                return new JsonResponse([
                    'error' => 'Ce membre est déjà inscrit à ce tournoi'
                ], 400);
            }
        }

        $participation = new Participation();
        $participation->setMember($membre);
        $participation->setTournament($tournoi);
        $participation->setStatus('Inscrit');
        $participation->setJoinedAt();

        $em->persist($participation);
        $em->flush();

        return new JsonResponse(['status' => 'Membre inscrit'], JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/api/tournois/{tournoiId}/membres/{membreId}',
        methods: ['DELETE'],
        name: 'app_tournoi_removeMember'
    )]
    public function removeMember(
        int $tournoiId,
        int $membreId,
        TournoiRepository $tournoiRepository,
        MembreRepository $membreRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $tournoi = $tournoiRepository->find($tournoiId);
        $membre = $membreRepository->find($membreId);

        if (!$tournoi) {
            return new JsonResponse(['error' => 'Tournoi introuvable'], 404);
        }

        if (!$membre) {
            return new JsonResponse(['error' => 'Membre introuvable'], 404);
        }

        $participationToRemove = null;

        foreach ($tournoi->getParticipations() as $participation) {
            if ($participation->getMember()->getId() === $membre->getId()) {
                $participationToRemove = $participation;
                break;
            }
        }

        if (!$participationToRemove) {
            return new JsonResponse([
                'error' => 'Membre non inscrit au tournoi'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $tournoi->removeParticipation($participationToRemove);
        $em->remove($participationToRemove);
        $em->flush();

        return new JsonResponse([
            'status' => 'Membre retiré du tournoi'
        ], JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/api/tournois/{id}/finish',
        methods: 'PATCH',
        name: 'app_tournoi_finish'
    )]
    public function finish(
        int $id,
        TournoiRepository $tournoiRepository,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $tournoi = $tournoiRepository->find($id);

        if (!$tournoi) {
            return new JsonResponse([
                'error' => 'Tournoi introuvable'
            ], 404);
        }

        if ($tournoi->getStatus() === "Terminé") {
            return new JsonResponse([
                "error" => "le tournoi est déjà terminé"
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $tournoi->setStatus('Terminé');
        $em->flush();

        return new JsonResponse(["status" => "le tournois est terminé"], JsonResponse::HTTP_OK);
    }
}
