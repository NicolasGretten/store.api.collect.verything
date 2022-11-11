<?php

namespace App\Http\Controllers;

use App\Models\Store;
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
     *      @OA\Parameter(name="title", description="Store title", required=true, in="query"),
     *      @OA\Parameter(name="storeLine1", description="Store line 1", required=true, in="query"),
     *      @OA\Parameter(name="storeLine2", description="Store line 2", in="query"),
     *      @OA\Parameter(name="zipCode", description="Zip code", required=true, in="query"),
     *      @OA\Parameter(name="city", description="City", required=true, in="query"),
     *      @OA\Parameter(name="country", description="Store country", required=true, in="query"),
     *      @OA\Response(response=201,description="Account created"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function addMedia(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'title'                     => 'required|string',
                'storeLine1'            => 'required|string',
                'storeLine2'            => 'string',
                'zipCode'                  => 'required|string',
                'city'                      => 'required|string',
                'state'                     => 'string',
                'country'                   => 'required|string',
            ]);

            DB::beginTransaction();

            $geocoding = app('geocoder')->geocode($request->input('storeLine1').', '.$request->input('zipCode').''.$request->input('city').''.$request->input('country'))->get()->first();

            $store = new Store();
            $store->id                        = $this->generateId('store', $store);
            $store->title                     = $request->input('title');
            $store->storeLine1            = $request->input('storeLine1');
            $store->storeLine2            = $request->input('storeLine2');
            $store->zipCode                  = $request->input('zipCode');
            $store->city                      = $request->input('city');
            $store->country                   = $request->input('country');
            $store->latitude                  = $geocoding->getCoordinates()->getLatitude();
            $store->longitude                 = $geocoding->getCoordinates()->getLongitude();

            $store->save();

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

            $store->delete();

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
