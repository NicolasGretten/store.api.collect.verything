<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreMedia;
use App\Traits\FiltersTrait;
use App\Traits\IdTrait;
use App\Traits\JwtTrait;
use App\Traits\PaginationTrait;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreMediaController extends Controller
{
    use JWTTrait, FiltersTrait, PaginationTrait, IdTrait;

    /**
     * @OA\Post(
     *      path="/api/stores/{id}/medias",
     *      operationId="addMedia",
     *      tags={"Stores"},
     *      summary="Post a new store media",
     *      description="Create a new store media",
     *      @OA\Parameter(name="id", description="Store Id", required=true, in="query"),
     *      @OA\Parameter(name="url", description="URL", required=true, in="query"),
     *      @OA\Parameter(name="type", description="Media type", required=true, in="query"),
     *      @OA\Response(response=201,description="Account created"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function addMedia(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url'  => 'required|string',
                'type'  => 'required|string',
            ]);

            DB::beginTransaction();

            $resultSet = Store::where('stores.id', $request->id);

            $store = $resultSet->first();
            if (empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }

            $storeClosing = new StoreMedia();
            $storeClosing->id           = $this->generateId('storemedia', $storeClosing);
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
     *      path="/api/stores/{id}/medias/{mediaId}",
     *      operationId="removeMedia",
     *      tags={"Stores"},
     *      summary="Delete a store media",
     *      description="Soft delete a store media",
     *      @OA\Parameter(
     *          name="id",
     *          description="Account id",
     *          required=true,
     *          in="path",
     *      ),
     *     @OA\Parameter(
     *          name="mediaId",
     *          description="Media id",
     *          required=true,
     *          in="path",
     *      ),
     *      @OA\Response(response=200, description="Account deleted"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function removeMedia(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $resultSet = Store::select('stores.*')->where('id', $request->id);

            $store = $resultSet->first();

            if(empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }


            $resultSet = StoreMedia::where('stores_medias.id', $request->media_id);

            $storeMedia = $resultSet->first();
            if (empty($storeMedia)) {
                throw new ModelNotFoundException('Media not found.', 404);
            }

            $storeMedia->delete();

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
