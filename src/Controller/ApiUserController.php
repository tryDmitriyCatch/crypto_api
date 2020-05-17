<?php

namespace App\Controller;

use App\Entity\UserEntity;
use App\Services\FindUserService;
use App\Services\Traits\DemTrait;
use App\Utils\PasswordUtils;
use App\Utils\UUID;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ApiUserController
 */
class ApiUserController extends AbstractController
{
    use DemTrait;

    /**
     * @var FindUserService $findUserService
     */
    private $findUserService;

    /**
     * ApiUserController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FindUserService $findUserService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FindUserService $findUserService
    )
    {
        $this->dem = $entityManager;
        $this->findUserService = $findUserService;
    }

    /**
     * Create new user
     *
     * @Route("/api/user/create", name="api_user_create")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     */
    public function userCreateAction(Request $request): JsonResponse
    {
        $newUser = $this->createUser($request);
        return new JsonResponse([
            'status' => 'ok',
            'message' => 'User has been successfully created',
            'data' => json_decode($newUser->getContent(), true, 512, JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    protected function createUser($request): JsonResponse
    {
        $userEntity = (new UserEntity())
            ->setToken(UUID::generate())
            ->setName($request->get('name'))
            ->setSurname($request->get('surname'))
            ->setEmail($request->get('email'))
            ->setPassword(PasswordUtils::hashPassword($request->get('password')));

        $this->persist($userEntity, true);

        return $this->json([
            'id' => $userEntity->getId(),
            'name' => $userEntity->getName(),
            'surname' => $userEntity->getSurname(),
            'email' => $userEntity->getEmail(),
        ]);
    }

    /**
     * Gets user information
     *
     * @Route("/api/user/{id}", name="api_user_view")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     */
    public function userIndexAction(Request $request): JsonResponse
    {
        /* @var UserEntity $userEntity */
        $user = $this->getUserById($request->get('id'));

        if (is_null($user) && !$user instanceof UserEntity)
        {
            $this->buildUserNotFoundResponse();
        }

        return $this->json([
            'status' => 'ok',
            'data' => json_decode($user->getContent(), true, 512, JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @param string $id
     * @return object|null
     */
    protected function getUserById($id)
    {
        /**
         * @var UserEntity|null
         */
        $userEntity = $this->findUserService->getUserById($id);

        if (is_null($userEntity)) {
            throw $this->createNotFoundException();
        }

        return $this->json([
            'id' => $userEntity->getId(),
            'name' => $userEntity->getName(),
            'surname' => $userEntity->getSurname(),
            'email' => $userEntity->getEmail(),
            'assets' => $userEntity->getAssets(),
        ]);
    }

    /**
     * @return JsonResponse
     */
    protected function buildUserNotFoundResponse(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => 'User not found',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Edit user information
     *
     * @Route("/api/user/{id}/update", name="api_user_update")
     * @Method({"PUT"})
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     */
    public function userEditAction(Request $request): JsonResponse
    {
        /* @var UserEntity $userEntity */
        $updatedUser = $this->updateUser($request);

        if (is_null($updatedUser) && !$updatedUser instanceof UserEntity)
        {
            $this->buildUserNotFoundResponse();
        }

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'User has been successfully updated',
            'data' => json_decode($updatedUser->getContent(), true, 512, JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    protected function updateUser($request): JsonResponse
    {
        /**
         * @var UserEntity|null
         */
        $userEntity = $this->findUserService->getUserById($request->get('id'));

        if (is_null($userEntity)) {
            throw $this->createNotFoundException();
        }

        $userEntity
            ->setName($request->get('name'))
            ->setSurname($request->get('surname'))
            ->setEmail($request->get('email'))
            ->setPassword(PasswordUtils::hashPassword($request->get('password')));

        $this->flush();

        return $this->json([
            'id' => $userEntity->getId(),
            'name' => $userEntity->getName(),
            'surname' => $userEntity->getSurname(),
            'email' => $userEntity->getEmail(),
            'assets' => $userEntity->getAssets(),
        ]);
    }

    /**
     * Delete user
     *
     * @Route("/api/user/{id}/delete", name="api_user_delete")
     * @Method({"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function userDeleteAction(Request $request): JsonResponse
    {
        $this->deleteUser($request);

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'User has been successfully deleted',
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function deleteUser($request): void
    {
        /**
         * @var UserEntity|null
         */
        $userEntity = $this->findUserService->getUserById($request->get('id'));

        if (is_null($userEntity)) {
            throw $this->createNotFoundException();
        }

        $this->remove($userEntity);
        $this->flush();
    }
}