<?php

namespace App\Controller;

use App\Entity\AssetEntity;
use App\Entity\UserEntity;
use App\Services\ExchangeAPIService;
use App\Services\FindUserDataService;
use App\Services\Traits\DemTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ApiAssetsController
 */
class ApiAssetsController extends AbstractController
{
    use DemTrait;

    /**
     * @var FindUserDataService $findUserService
     */
    private $findUserService;

    /**
     * @var ExchangeAPIService $exchangeService
     */
    private $exchangeService;

    /**
     * ApiAssetsController constructor.
     * @param EntityManagerInterface $entityManager
     * @param ExchangeAPIService $exchangeService
     * @param FindUserDataService $findUserService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ExchangeAPIService $exchangeService,
        FindUserDataService $findUserService
    )
    {
        $this->dem = $entityManager;
        $this->exchangeService = $exchangeService;
        $this->findUserService = $findUserService;
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
            ->setUser($this->getRepository(UserEntity::class)->findByToken($request->get('token')));

        $this->persist($assetEntity, true);

        return $assetEntity;
    }

    /**
     * Get User's Assets
     *
     * #### Response example (JSON) ###
     *
     *     {
     *       "status": "ok",
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
     *  description="Gets requested user's assets",
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
     */
    public function userAssetIndexAction(Request $request): JsonResponse
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
     *           "currency": 1
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
            'message' => 'Asset has been successfully updated',
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

        $assetEntity = $this->deleteAsset($request);

        return $this->json([
            'status' => 'success',
            'message' => 'Asset has been successfully deleted',
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
            ->setValue($request->get('value'));

        $this->flush();

        return $this->json([
            'id' => $assetEntity->getId(),
            'label' => $assetEntity->getLabel(),
            'value' => $assetEntity->getValue(),
            'currency' => $assetEntity->getCurrency(),
        ]);
    }

    /**
     * @param AssetEntity $assetEntity
     * @return array
     */
    protected function getAssetDataAsArray($assetEntity): array
    {
        return [
            'id' => $assetEntity->getId(),
            'label' => $assetEntity->getLabel(),
            'value' => $assetEntity->getValue(),
            'currency' => $assetEntity->getCurrency(),
        ];
    }

    /**
     * @return JsonResponse
     */
    protected function buildTokenAndIdMissingResponse(): JsonResponse
    {
        return $this->json([
            'status'   => 'error',
            'message'  => 'Token and/or ID is missing',
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return JsonResponse
     */
    protected function buildAssetNotFoundResponse(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => 'Asset not found',
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return JsonResponse
     */
    protected function buildMissingParametersResponse(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => 'One or more parameters missing',
        ], Response::HTTP_BAD_REQUEST);
    }
}
