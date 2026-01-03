<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Repository\MembreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class MembreController extends AbstractController
{
    public function index(MembreRepository $membreRepository): JsonResponse
    {
        $membres = $membreRepository->findAll();

        $data = array_map( function (Membre $membre) {
            return [
                'id' => $membre->getId(),
                'firstName' => $membre->getName(),
                'lastName' => $membre->getLastName(),
                'email' => $membre->getEmail(),
                'roles' => $membre->getRoles(),
            ];
        }, $membres);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    public function show(Membre $membre): JsonResponse
    {
        $data = [
                'id' => $membre->getId(),
                'firstName' => $membre->getName(),
                'lastName' => $membre->getLastName(),
                'email' => $membre->getEmail(),
                'roles' => $membre->getRoles(),
            ];
            return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
