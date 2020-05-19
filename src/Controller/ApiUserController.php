<?php

namespace App\Controller;

use App\Entity\UserEntity;
use App\Services\FindUserService;
use App\Services\Traits\DemTrait;
use App\Utils\PasswordUtils;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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
     * Get User Information
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "ok",
     *       "data":  {
     *           "id": 3,
     *           "name": "Name",
     *           "surname": "Surname",
     *           "email": "example@email.com",
     *           "assets": []
     *           },
     *     }
     *
     * @ApiDoc(
     *  section="User Actions",
     *  resource=true,
     *  description="Gets requested user's information",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *      {"name"="id", "dataType"="string", "required"=true, "format"="1", "description"="Users's Unique ID"},
     *  },
     *  statusCodes={
     *      200="Success",
     *      404="User not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/user/", name="api_user_view")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function userIndexAction(Request $request): JsonResponse
    {
        if (empty($request->get('token'))) {
            $this->buildTokenMissingResponse();
        }

        /* @var UserEntity $userEntity */
        $user = $this->getUserById($request->get('id'));

        if (is_null($user) && !$user instanceof UserEntity)
        {
            $this->buildUserNotFoundResponse();
        }

        return $this->json([
            'status' => 'ok',
            'data' => json_decode($user->getContent(), true),
        ]);
    }

    /**
     * @return JsonResponse
     */
    protected function buildTokenMissingResponse(): JsonResponse
    {
        return new JsonResponse([
            'status'   => 'error',
            'message'  => 'Token is missing',
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
     * Edit User Information
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "ok",
     *       "message" => "User has been successfully updated",
     *       "data":  {
     *           "id": 3,
     *           "name": "Name",
     *           "surname": "Surname",
     *           "email": "example@email.com",
     *           "assets": []
     *           },
     *     }
     *
     * @ApiDoc(
     *  section="User Actions",
     *  resource=true,
     *  description="Edit requested user's information",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *      {"name"="name", "dataType"="string", "required"=true, "format"="John", "description"="Users's Name"},
     *      {"name"="surname", "dataType"="string", "required"=true, "format"="Doe", "description"="Users's Surname"},
     *      {"name"="email", "dataType"="string", "required"=true, "format"="example@email.com", "description"="Users's Personal Code"},
     *      {"name"="password", "dataType"="string", "required"=true, "format"="test", "description"="Users's Password"},
     *  },
     *  statusCodes={
     *      200="Success",
     *      404="User not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/user/", name="api_user_update")
     * @Method({"PUT", "PATCH"})
     * @param Request $request
     * @return JsonResponse
     */
    public function userEditAction(Request $request): JsonResponse
    {
        if (empty($request->get('token'))) {
            $this->buildTokenMissingResponse();
        }

        /* @var UserEntity $userEntity */
        $updatedUser = $this->updateUser($request);

        if (is_null($updatedUser) && !$updatedUser instanceof UserEntity)
        {
            $this->buildUserNotFoundResponse();
        }

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'User has been successfully updated',
            'data' => json_decode($updatedUser->getContent(), true),
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
            ->setUpdatedAt(new DateTimeImmutable())
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
     * Delete User
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "ok",
     *       "message" => "User has been successfully deleted",
     *     }
     *
     * @ApiDoc(
     *  section="User Actions",
     *  resource=true,
     *  description="Delete's User",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *      {"name"="id", "dataType"="string", "required"=true, "format"="1", "description"="Users's Unique ID"},
     *  },
     *  statusCodes={
     *      200="Success",
     *      404="User not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/user/", name="api_user_delete")
     * @Method({"DELETE"})
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