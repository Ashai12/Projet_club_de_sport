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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class MembreController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/api/membres', methods: ['GET'], name: 'app_membre_index')]
    public function index(MembreRepository $membreRepository): JsonResponse
    {
        $membres = $membreRepository->findAll();

        $data = array_map(function (Membre $membre) {
            return [
                'id' => $membre->getId(),
                'prénom' => $membre->getName(),
                'nom' => $membre->getLastName(),
                'email' => $membre->getUserIdentifier(),
                'roles' => $membre->getRoles(),
            ];
        }, $membres);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/api/membres/{id}', methods: ['GET'], name: 'app_membre_show')]
    public function show(Membre $membre): JsonResponse
    {
        $data = [
            'id' => $membre->getId(),
            'prénom' => $membre->getName(),
            'nom' => $membre->getLastName(),
            'email' => $membre->getUserIdentifier(),
            'roles' => $membre->getRoles(),
        ];
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/membres', methods: ['POST'], name: 'app_membre_create')]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new Membre;

        $user->setName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setEmail($data['email']);
        $user->setPlainPassword($data['password']);
        $user->setRoles(['ROLE_USER']);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $messages = [];

            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return new JsonResponse([
                'errors' => $messages
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $user->getPlainPassword()
        );

        $user->setPassword($hashedPassword);
        $user->setPlainPassword(null);

        // Enregistrement en db
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'Utilisateur créé'], JsonResponse::HTTP_CREATED);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/api/membres/{id}', methods: ['PATCH'], name: 'app_membre_update')]
    public function update(Request $request, Membre $user, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {
        $currentUser = $this->getUser();

        // Vérification si ADMIN si oui on passe au flush
        if (!$this->isGranted('ROLE_ADMIN')) {
            // Vérification si c'est son propre profil
            if (!$currentUser || !($currentUser instanceof Membre) || $user->getId() !== $currentUser->getId()) {
                throw $this->createAccessDeniedException('Tu ne peux pas modifier ce profil');
            }
        }

        $data = json_decode($request->getContent(), true);

        // Mise à jour des champs
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
            $user->setPlainPassword($data['password']);
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }


        // Choix des groupes de validation
        $groups = ['profile_update'];
        if (isset($data['password'])) {
            $groups[] = 'password_update';
        }

        // Validation
        $errors = $validator->validate($user, null, $groups);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()][] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $messages], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Sauvegarde
        $em->flush();

        return new JsonResponse(['status' => 'Utilisateur modifié'], JsonResponse::HTTP_OK);
    }


    #[Route('/api/membres/{id}', methods: ['DELETE'], name: 'app_membre_delete')]
    public function delete(Membre $user, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$currentUser || !($currentUser instanceof Membre)) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié');
        }

        // ADMIN peut supprimer tout le monde
        // USER peut seulement supprimer son propre compte
        if (!in_array('ROLE_ADMIN', $currentUser->getRoles()) && $currentUser->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Tu ne peux pas supprimer ce profil');
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['status' => 'Utilisateur supprimé'], JsonResponse::HTTP_OK);
    }

}
