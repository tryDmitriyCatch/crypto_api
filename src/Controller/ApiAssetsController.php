<?php

namespace App\Controller;

use App\Entity\AssetEntity;
use App\Entity\UserEntity;
use App\Exception\AppException;
use App\Services\FindUserDataService;
use App\Services\Traits\DemTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ApiAssetsController
 */
class ApiAssetsController extends AbstractController
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
     * ApiAssetsController constructor.
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
     * Get User's Assets
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "created",
     *       "data":  {
     *           "id": 1,
     *           "label": "bike",
     *           "value": "1.99",
     *           "currency": 1,
     *           "value_in_USD": "18035.708 USD",
     *        }, {
     *           "id": 2,
     *           "label": "House",
     *           "value": "1.99",
     *           "currency": 1,
     *           "value_in_USD": "18035.708 USD",
     *        },
     *     }
     *
     * @ApiDoc(
     *  section="User Asset Actions",
     *  resource=true,
     *  description="Gets all assets",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *  },
     *  statusCodes={
     *      201="Success",
     *      404="Asset not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/asset/index", name="api_asset_index", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function userAssetIndexAction(Request $request): JsonResponse
    {
        if (empty($request->get('token'))) {
            return $this->buildTokenAndIdMissingResponse();
        }

        /* @var UserEntity $userEntity */
        $userEntity = $this->getRepository(UserEntity::class)->findByToken($request->get('token'));
        $assetEntity = $this->getRepository(AssetEntity::class)->findByUserId($userEntity->getId());

        return $this->json([
            'status' => 'success',
            'data' => $this->findUserService->returnArrayOfUserAssets(null, $assetEntity),
            'total_assets_in_USD' => $this->findUserService->returnTotalAssetValuesInUsd($assetEntity),
        ], Response::HTTP_OK);
    }

    /**
     * Create User's Asset
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "created",
     *       "data":  {
     *           "id": 1,
     *           "label": "bike",
     *           "value": "1.99",
     *           "currency": 1
     *        },
     *     }
     *
     * @ApiDoc(
     *  section="User Asset Actions",
     *  resource=true,
     *  description="Creates new asset",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *      {"name"="label", "dataType"="string", "required"="true", "format"="Bike", "description"="Asset's Label"},
     *      {"name"="currency", "dataType"="integer", "required"="true", "format"="1", "description"="Asset's Currency. 1 for BTC, 2 for ETH, 3 for IOTA"},
     *      {"name"="value", "dataType"="decimal", "required"="true", "format"="1.99", "description"="Asset's Value"},
     *  },
     *  statusCodes={
     *      201="Created",
     *      404="Asset not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/asset/create", name="api_asset_create", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     */
    public function userAssetCreateAction(Request $request): JsonResponse
    {
        if (empty($request->get('token'))) {
            return $this->buildTokenAndIdMissingResponse();
        }

        if (empty($request->get('label')) || empty($request->get('currency')) || empty($request->get('value')))
        {
            return $this->buildMissingParametersResponse();
        }

        $assetEntity = $this->createAssetEntity($request);

        return $this->json([
            'status' => 'created',
            'data' => $this->findUserService->returnArrayOfUserAssets($assetEntity->getUser()),
        ], Response::HTTP_CREATED);
    }

    /**
     * @param $request
     * @return AssetEntity
     */
    protected function createAssetEntity($request): AssetEntity
    {
        $assetEntity = (new AssetEntity())
            ->setLabel($request->get('label'))
            ->setCurrency($request->get('currency'))
            ->setValue($request->get('value'))
            ->setCreatedAt(new DateTimeImmutable())
            ->setUser($this->getRepository(UserEntity::class)->findByToken($request->get('token')));

        $this->persist($assetEntity, true);

        return $assetEntity;
    }

    /**
     * Get User's Asset
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "ok",
     *       "data":  {
     *           "id": 1,
     *           "label": "bike",
     *           "value": "1.99",
     *           "currency": 1,
     *           "value_in_USD": "18048.818 USD",
     *        },
     *     }
     *
     * @ApiDoc(
     *  section="User Asset Actions",
     *  resource=true,
     *  description="Gets requested user's asset",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *      {"name"="asset_id", "dataType"="string", "required"="true", "format"="1", "description"="Asset Unique ID"},
     *  },
     *  statusCodes={
     *      200="Success",
     *      404="Asset not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/asset/{asset_id}", name="api_user_asset", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     */
    public function userAssetViewAction(Request $request): JsonResponse
    {
        if (empty($request->get('token')) && empty($request->get('asset_id'))) {
            return $this->buildTokenAndIdMissingResponse();
        }

        /* @var AssetEntity $assetEntity */
        $assetEntity = $this->getRepository(AssetEntity::class)->find($request->get('asset_id'));

        if (is_null($assetEntity) && !$assetEntity instanceof AssetEntity)
        {
            return $this->buildAssetNotFoundResponse();
        }

        return $this->json([
            'status' => 'ok',
            'data' => $this->getAssetDataAsArray($assetEntity),
        ], Response::HTTP_OK);
    }

    /**
     * Update User's Asset
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "success",
     *       "message": "Asset has been successfully updated",
     *       "data":  {
     *           "id": 1,
     *           "label": "bike",
     *           "value": "1.99",
     *           "currency": 1,
     *           "value_in_USD": "18048.818 USD",
     *        },
     *     }
     *
     * @ApiDoc(
     *  section="User Asset Actions",
     *  resource=true,
     *  description="Updates asset",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *      {"name"="asset_id", "dataType"="string", "required"="true", "format"="1", "description"="Asset Unique ID"},
     *      {"name"="label", "dataType"="string", "required"="true", "format"="Bike", "description"="Asset's Label"},
     *      {"name"="currency", "dataType"="integer", "required"="true", "format"="1", "description"="Asset's Currency. 1 for BTC, 2 for ETH, 3 for IOTA"},
     *      {"name"="value", "dataType"="decimal", "required"="true", "format"="1.99", "description"="Asset's Value"},
     *  },
     *  statusCodes={
     *      201="Success",
     *      404="Asset not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/asset/update", name="api_asset_update", methods={"PUT", "PATCH"})
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     */
    public function userAssetUpdateAction(Request $request): JsonResponse
    {
        if (empty($request->get('token'))) {
            return $this->buildTokenAndIdMissingResponse();
        }

        if (empty($request->get('asset_id')) || empty($request->get('label')) || empty($request->get('currency')) || empty($request->get('value')))
        {
            return $this->buildMissingParametersResponse();
        }

        $assetEntity = $this->updateAsset($request);

        return $this->json([
            'status' => 'success',
            'message' => $this->translator->trans('asset_updated'),
            'data' => json_decode($assetEntity->getContent(), true),
        ], Response::HTTP_OK);
    }

    /**
     * Delete User's Asset
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "success",
     *       "message": "Asset has been successfully deleted",
     *     }
     *
     * @ApiDoc(
     *  section="User Asset Actions",
     *  resource=true,
     *  description="Deletes asset",
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "format"="55a660e5-85ee-530c-2791-c25b7a2b0216", "description"="API Token Key"},
     *      {"name"="asset_id", "dataType"="string", "required"="true", "format"="1", "description"="Asset Unique ID"},
     *  },
     *  statusCodes={
     *      201="Success",
     *      404="Asset not found",
     *      500="Technical Problems Processing the Request"
     *  }
     * )
     * @Route("/api/asset/delete", name="api_asset_delete", methods={"DELETE"})
     * @param Request $request
     * @return JsonResponse
     */
    public function userAssetDeleteAction(Request $request): JsonResponse
    {
        if (empty($request->get('token'))) {
            return $this->buildTokenAndIdMissingResponse();
        }

        if (empty($request->get('asset_id')))
        {
            return $this->buildMissingParametersResponse();
        }

        $this->deleteAsset($request);

        return $this->json([
            'status' => 'success',
            'message' => $this->translator->trans('asset_deleted'),
        ], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    protected function deleteAsset($request): JsonResponse
    {
        /** @var AssetEntity|null */
        $assetEntity = $this->getRepository(AssetEntity::class)->find($request->get('asset_id'));

        if (is_null($assetEntity)) {
            return $this->buildMissingParametersResponse();
        }

        $this->remove($assetEntity);
        $this->flush();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     */
    protected function updateAsset($request): JsonResponse
    {
        /** @var AssetEntity|null */
        $assetEntity = $this->getRepository(AssetEntity::class)->find($request->get('asset_id'));

        if (is_null($assetEntity)) {
            return $this->buildMissingParametersResponse();
        }

        $assetEntity
            ->setLabel($request->get('label'))
            ->setCurrency($request->get('currency'))
            ->setValue($request->get('value'))
            ->setUpdatedAt(new DateTimeImmutable());

        $this->flush();

        return $this->json([
            'id' => $assetEntity->getId(),
            'label' => $assetEntity->getLabel(),
            'value' => $assetEntity->getValue(),
            'currency' => $assetEntity->getCurrency(),
            'value_in_USD' => $this->findUserService->getCurrentAssetExchangeRateSum($assetEntity),
        ]);
    }

    /**
     * @param AssetEntity $assetEntity
     * @return array
     * @throws AppException
     */
    protected function getAssetDataAsArray($assetEntity): array
    {
        return [
            'id' => $assetEntity->getId(),
            'label' => $assetEntity->getLabel(),
            'value' => $assetEntity->getValue(),
            'currency' => $assetEntity->getCurrency(),
            'value_in_USD' => $this->findUserService->getCurrentAssetExchangeRateSum($assetEntity),
        ];
    }

    /**
     * @return JsonResponse
     */
    protected function buildTokenAndIdMissingResponse(): JsonResponse
    {
        return $this->json([
            'status'   => 'error',
            'message'  => $this->translator->trans('asset_token_not_found'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return JsonResponse
     */
    protected function buildAssetNotFoundResponse(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => $this->translator->trans('asset_not_found'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return JsonResponse
     */
    protected function buildMissingParametersResponse(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => $this->translator->trans('asset_params_missing'),
        ], Response::HTTP_BAD_REQUEST);
    }
}
