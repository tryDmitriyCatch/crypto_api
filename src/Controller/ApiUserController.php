<?php

namespace App\Controller;

use App\Entity\UserEntity;
use App\Exception\AppException;
use App\Services\FindUserDataService;
use App\Services\Traits\DemTrait;
use App\Utils\PasswordUtils;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ApiUserController
 */
class ApiUserController extends AbstractController
{
    use DemTrait;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FindUserDataService $findUserService
     */
    private $findUserService;

    /**
     * ApiUserController constructor.
     * @param EntityManagerInterface $entityManager
     * @param FindUserDataService $findUserService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FindUserDataService $findUserService,
        TranslatorInterface $translator
    )
    {
        $this->dem = $entityManager;
        $this->findUserService = $findUserService;
        $this->translator = $translator;
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
     *           "assets": [
     *               {
     *               "id": 1,
     *               "label": "bike",
     *               "value": "1.99",
     *               "currency": 1
     *               },
     *            ]
     *         },
     *     }
     *
     * @ApiDoc(
     *  section="User Actions",
     *  resource=true,
     *  description="Gets requested user's information",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *  },
     *  statusCodes={
     *      200="Success",
     *      404="User not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/user/", name="api_user_view", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     */
    public function userIndexAction(Request $request): JsonResponse
    {
        if (empty($request->get('token'))) {
            return $this->buildTokenMissingResponse();
        }

        /* @var UserEntity $userEntity */
        $user = $this->getUserByToken($request->get('token'));

        if (is_null($user) && !$user instanceof UserEntity)
        {
            return $this->buildUserNotFoundResponse();
        }

        return $this->json([
            'status' => 'ok',
            'data' => json_decode($user->getContent(), true),
        ], Response::HTTP_OK);
    }

    /**
     * @return JsonResponse
     */
    protected function buildTokenMissingResponse(): JsonResponse
    {
        return $this->json([
            'status'   => 'error',
            'message'  => $this->translator->trans('asset_token_not_found'),
        ]);
    }

    /**
     * @param string $token
     * @return object|null
     * @throws AppException
     */
    protected function getUserByToken($token)
    {
        /** @var UserEntity|null */
        $userEntity = $this->findUserService->getUserByToken($token);

        if (is_null($userEntity)) {
            return $this->buildTokenMissingResponse();
        }

        return $this->json([
            'id' => $userEntity->getId(),
            'name' => $userEntity->getName(),
            'surname' => $userEntity->getSurname(),
            'email' => $userEntity->getEmail(),
            'assets' => $this->findUserService->returnArrayOfUserAssets($userEntity->getId()),
        ]);
    }

    /**
     * @return JsonResponse
     */
    protected function buildUserNotFoundResponse(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => $this->translator->trans('user_not_found'),
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Edit User Information
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
     *           "assets": [
     *               {
     *               "id": 1,
     *               "label": "bike",
     *               "value": "1.99",
     *               "currency": 1
     *               },
     *            ]
     *         },
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
     * @Route("/api/user/", name="api_user_update", methods={"PUT", "PATCH"})
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     */
    public function userEditAction(Request $request): JsonResponse
    {
        if (empty($request->get('token'))) {
            return $this->buildTokenMissingResponse();
        }

        /* @var UserEntity $userEntity */
        $updatedUser = $this->updateUser($request);

        if (is_null($updatedUser) && !$updatedUser instanceof UserEntity)
        {
            return $this->buildUserNotFoundResponse();
        }

        return $this->json([
            'status' => 'ok',
            'message' => $this->translator->trans('user_updated'),
            'data' => json_decode($updatedUser->getContent(), true),
        ], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     */
    protected function updateUser($request): JsonResponse
    {
        /**
         * @var UserEntity|null
         */
        $userEntity = $this->findUserService->getUserByToken($request->get('token'));

        if (is_null($userEntity)) {
            return $this->buildTokenMissingResponse();
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
            'assets' => $this->findUserService->returnArrayOfUserAssets($userEntity->getId()),
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
     *  },
     *  statusCodes={
     *      200="Success",
     *      404="User not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/user/", name="api_user_delete", methods={"DELETE"})
     * @param Request $request
     * @return JsonResponse
     */
    public function userDeleteAction(Request $request): JsonResponse
    {
        $this->deleteUser($request);

        return $this->json([
            'status' => 'ok',
            'message' => $this->translator->trans('user_deleted'),
        ], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    protected function deleteUser($request): JsonResponse
    {
        /**
         * @var UserEntity|null
         */
        $userEntity = $this->findUserService->getUserByToken($request->get('token'));

        if (is_null($userEntity)) {
            return $this->buildTokenMissingResponse();
        }

        $this->remove($userEntity);
        $this->flush();
    }
}
