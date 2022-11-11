<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreClosing;
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

class StoreClosingController extends Controller
{
    use JWTTrait, FiltersTrait, PaginationTrait, IdTrait;

    /**
     * @OA\Post(
     *      path="/api/stores/{id}/closings",
     *      operationId="addClosing",
     *      tags={"Stores"},
     *      summary="Post a new store closing",
     *      description="Create a new store closing",
     *      @OA\Parameter(name="id", description="Store Id", required=true, in="query"),
     *      @OA\Parameter(name="from", description="From", required=true, in="query"),
     *      @OA\Parameter(name="to", description="To", required=true, in="query"),
     *      @OA\Response(response=201,description="Account created"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function addClosing(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'from'  => 'required|string',
                'to'    => 'required|string',
            ]);

            DB::beginTransaction();

            $resultSet = Store::where('stores.id', $request->id);

            $store = $resultSet->first();
            if (empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }

            $storeClosing = new StoreClosing();
            $storeClosing->id           = $this->generateId('storeclosing', $storeClosing);
            $storeClosing->store_id     = $store->id;
            $storeClosing->from         = $request->input('from');
            $storeClosing->to           = $request->input('to');

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
     *      path="/api/stores/{id}/closings/{closing_id}",
     *      operationId="removeClosing",
     *      tags={"Stores"},
     *      summary="Delete a store closing",
     *      description="Soft delete a store closing",
     *      @OA\Parameter(
     *          name="id",
     *          description="Account id",
     *          required=true,
     *          in="path",
     *      ),
     *     @OA\Parameter(
     *          name="closing_id",
     *          description="Closing id",
     *          required=true,
     *          in="path",
     *      ),
     *      @OA\Response(response=200, description="Closing deleted"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function removeClosing(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $resultSet = Store::where('stores.id', $request->id);

            $store = $resultSet->first();
            if (empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }

            $resultSet = StoreClosing::where('stores_closings.id', $request->closing_id);

            $storeClosing = $resultSet->first();
            if (empty($storeClosing)) {
                throw new ModelNotFoundException('Closing not found.', 404);
            }

            $storeClosing->delete();

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
