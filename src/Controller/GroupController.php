<?php

namespace App\Controller;

use App\Entity\Group;
use App\Repository\GroupRepository;
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
}
