<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreImage;
use App\Traits\FiltersTrait;
use App\Traits\IdTrait;
use App\Traits\JwtTrait;
use App\Traits\PaginationTrait;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreImageController extends Controller
{
    use JWTTrait, FiltersTrait, PaginationTrait, IdTrait;

    /**
     * @OA\Post(
     *      path="/api/stores/{id}/images",
     *      operationId="addImage",
     *      tags={"Stores"},
     *      summary="Post a new store image",
     *      description="Create a new store image",
     *      @OA\Parameter(name="id", description="Store Id", required=true, in="query"),
     *      @OA\Parameter(name="image_id", description="Image Id", required=true, in="query"),
     *      @OA\Response(response=201,description="Account created"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function addImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image_id'  => 'required|string',
            ]);

            DB::beginTransaction();

            $resultSet = Store::where('stores.id', $request->id);

            $store = $resultSet->first();
            if (empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }

            $storeClosing = new StoreImage();
            $storeClosing->id           = $this->generateId('storeimage', $storeClosing);
            $storeClosing->store_id     = $store->id;
            $storeClosing->image_id     = $request->input('image_id');

            $storeClosing->save();

            DB::commit();

            return response()->json($store->fresh(), 201);
        }
        catch(JsonEncodingException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), $e->getCode());
        }
        catch(ValidationException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
        catch(Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete  (
     *      path="/api/stores/{id}/images/{image_id}",
     *      operationId="removeImage",
     *      tags={"Stores"},
     *      summary="Delete a store image",
     *      description="Soft delete a store image",
     *      @OA\Parameter(
     *          name="id",
     *          description="Store id",
     *          required=true,
     *          in="path",
     *      ),
     *     @OA\Parameter(
     *          name="image_id",
     *          description="Image id",
     *          required=true,
     *          in="path",
     *      ),
     *      @OA\Response(response=200, description="Account deleted"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function removeImage(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $resultSet = Store::where('stores.id', $request->id);

            $store = $resultSet->first();
            if (empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }

            $resultSet = StoreImage::where('stores_images.id', $request->store_image_id);

            $storeImage = $resultSet->first();
            if (empty($storeImage)) {
                throw new ModelNotFoundException('Image not found.', 404);
            }

            $storeImage->delete();

            DB::commit();

            return response()->json($store->fresh(), 200);
        }
        catch(ModelNotFoundException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), $e->getCode());
        }
        catch(ValidationException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
        catch(AuthenticationException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 403);
        }
        catch(Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 500);
        }
    }
}
