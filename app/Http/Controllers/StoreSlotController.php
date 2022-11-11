<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreSlots;
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

class StoreSlotController extends Controller
{
    use JWTTrait, FiltersTrait, PaginationTrait, IdTrait;

    /**
     * @OA\Post(
     *      path="/api/stores/{id}/slots",
     *      operationId="addSlot",
     *      tags={"Stores"},
     *      summary="Post a new store slot",
     *      description="Create a new store slot",
     *      @OA\Parameter(name="day", description="Day", required=true, in="query"),
     *      @OA\Parameter(name="from", description="From", required=true, in="query"),
     *      @OA\Parameter(name="to", description="To", in="query"),
     *      @OA\Parameter(name="quantity", description="Quantity", required=true, in="query"),
     *      @OA\Parameter(name="available", description="Availability", required=true, in="query"),
     *      @OA\Response(response=201,description="Account created"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function addSlot(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'day'               => 'required|string',
                'from'              => 'required|string',
                'to'                => 'required|string',
                'quantity'          => 'required|string',
                'available'         => 'required|string',
            ]);

            DB::beginTransaction();

            $store = new StoreSlots();
            $store->id                      = $this->generateId('slot', $store);
            $store->day                   = $request->input('day');
            $store->from              = $request->input('from');
            $store->to              = $request->input('to');
            $store->quantity                 = $request->input('quantity');
            $store->available                    = $request->input('available');

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
     *      path="/api/stores/{id}/slots/{slotId}",
     *      operationId="removeSlot",
     *      tags={"Stores"},
     *      summary="Delete a store slot",
     *      description="Soft delete a store slot",
     *      @OA\Parameter(
     *          name="id",
     *          description="Store id",
     *          required=true,
     *          in="path",
     *      ),
     *     @OA\Parameter(
     *          name="slotId",
     *          description="Slot id",
     *          required=true,
     *          in="path",
     *      ),
     *      @OA\Response(response=200, description="Account deleted"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function removeSlot(Request $request): JsonResponse
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
