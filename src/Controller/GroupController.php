<?php

namespace App\Controller;

use App\Entity\Group;
use App\Repository\GroupRepository;
use Doctrine\ORM\Query\Expr\Func;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GroupController extends AbstractController
{
    #[isGranted('ROLE_USER')]
    #[Route('/api/groupes', methods: ['GET'], name: 'app_group_index')]
    public function index(GroupRepository $groupRepository): JsonResponse
    {
        $groups = $groupRepository->findAll();

        $data = array_map(function (Group $group) {
            return [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'members' => $group->getMembers(),
            ];
        }, $groups);
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/api/groupes/{id}', methods: ['GET'], name: 'app_group_show')]
    public function show(Group $group): JsonResponse
    {
        $data = [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'members' => $group->getMembers(),
        ];
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
