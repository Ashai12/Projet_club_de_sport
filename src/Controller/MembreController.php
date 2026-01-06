<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\VarDumper\Cloner\Data;

class MembreController extends AbstractController
{
    #[Route('/api/membres', methods: ['GET'])]
    public function index(MembreRepository $membreRepository): JsonResponse
    {
        $membres = $membreRepository->findAll();

        $data = array_map( function (Membre $membre) {
            return [
                'id' => $membre->getId(),
                'firstName' => $membre->getName(),
                'lastName' => $membre->getLastName(),
                'email' => $membre->getUserIdentifier(),
                'roles' => $membre->getRoles(),
            ];
        }, $membres);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/api/membres/{id}', methods: ['GET'])]
    public function show(Membre $membre): JsonResponse
    {
        $data = [
                'id' => $membre->getId(),
                'firstName' => $membre->getName(),
                'lastName' => $membre->getLastName(),
                'email' => $membre->getUserIdentifier(),
                'roles' => $membre->getRoles(),
            ];
            return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/api/membres', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new Membre;

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );

        $user->setName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setEmail($data['email']);
        $user->setPassword($hashedPassword);
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        // Enregistrement en db
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'User Created'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/membres/{id}', methods: ['PATCH'])]
    public function update(Request $request, Membre $user, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['firstName'])) {
            $user->setName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );
            $user->setPassword($hashedPassword);
        }

        // Sauvegarde des modifications
        $em->flush();

        return new JsonResponse(['status' => 'User updated'], JsonResponse::HTTP_OK);
    }
}
