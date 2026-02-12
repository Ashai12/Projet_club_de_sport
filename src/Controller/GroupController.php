<?php

namespace App\Controller;

use App\Entity\Group;
use App\Repository\GroupRepository;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Func;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class GroupController extends AbstractController
{
    #[isGranted('ROLE_USER')]
    #[Route('/api/groupes', methods: ['GET'], name: 'app_group_index')]
    public function index(GroupRepository $groupRepository): JsonResponse
    {
        $groups = $groupRepository->findAll();

        $data = array_map(function (Group $group) {

         $members = array_map(function ($member) {
            return [
                'id' => $member->getId(),
                'name' => $member->getName(),
                'email' => $member->getEmail(),
            ];
        }, $group->getMembers()->toArray());

            return [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'members' => $members,
            ];
        }, $groups);
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/api/groupes/{id}', methods: ['GET'], name: 'app_group_show')]
    public function show(Group $group): JsonResponse
    {
        $members = array_map(function ($member) {
            return [
                'id' => $member->getId(),
                'name' => $member->getName(),
                'email' => $member->getEmail(),
            ];
        }, $group->getMembers()->toArray());

        $data = [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'members' => $members,
        ];
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/groupes', methods: ['POST'], name: 'app_group_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        MembreRepository $membreRepository
        ): JsonResponse
    {
        $data =  json_decode($request->getContent(), true);

        if (!$data || !isset($data['groupName'], $data['membersId'])) {
            return new JsonResponse([
                'error' => 'Données invalides'
            ], 400);
        }


        $group = new Group;
        $group->setName($data['groupName']);

        foreach ($data['membersId'] as $memberId) {
        $member = $membreRepository->find($memberId);

        if (!$member) {
            return new JsonResponse([
                'error' => "Membre $memberId introuvable"
            ], 404);
        }

        $group->addMember($member);
    }

        $errors = $validator->validate($group);

        if (count($errors) > 0) {
            $messages = [];

            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return new JsonResponse([
                'errors' => $messages
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->persist($group);
        $em->flush();

        return new JsonResponse(['status' => 'Groupe créé'], JsonResponse::HTTP_CREATED);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/groupes/{id}', methods: ['POST'], name: 'app_group_addMember')]
    public function addMember(
        Group $group,
        Request $request,
        EntityManagerInterface $em,
        MembreRepository $membreRepository
        ): JsonResponse
    {
        $data =  json_decode($request->getContent(), true);
        $member = $membreRepository->find($data['membersId']);

        if(!$member) {
            return new JsonResponse(['error' => 'Membre introuvable'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($group->getMembers()->contains($member)) {
            return new JsonResponse([
                'error' => 'Ce membre est déjà dans le groupe.'
            ], 400);
        }

        $group->addMember($member);
        $em->flush();

        return new JsonResponse(['status' => 'Membre ajouté'], JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/groupes/{id}', methods: ['PATCH'], name: 'app_group_updateGroupName')]
    public function updateGroupName(
        Request $request,
        Group $group,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['groupName'])) {
            return new JsonResponse([
                'error' => 'Données invalides'
            ], 400);
        }

        $group->setName($data['groupName']);

        $errors = $validator->validate($group);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()][] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $messages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return new JsonResponse(['status' => 'Nom du groupe modifié'], JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('api/groupes/{id}', methods: ['DELETE'], name: 'app_groupe_delete')]
    public function delete(
        Group $group,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $em->remove($group);
        $em->flush();

        return new JsonResponse(['status' => 'Groupe supprimé'], JsonResponse::HTTP_OK);
    }
}
