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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreTranslationController extends Controller
{
    use JWTTrait, FiltersTrait, PaginationTrait, IdTrait;

    /**
     * @OA\Post(
     *      path="/api/stores/{id}/translations",
     *      operationId="addTranslation",
     *      tags={"Stores"},
     *      summary="Post a new store translation",
     *      description="Create a new store translation",
     *      @OA\Parameter(name="id", description="Store id", required=true, in="query"),
     *      @OA\Parameter(name="locale", description="Locale", required=true, in="query"),
     *      @OA\Parameter(name="description", description="Description", required=true, in="query"),
     *      @OA\Response(response=201,description="Account created"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function addTranslation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'locale'            => 'required|string',
                'description'       => 'required|string'
            ]);

            DB::beginTransaction();

            $resultSet = Store::where('stores.id', $request->id);

            $store = $resultSet->first();
            if (empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }

            $store = $resultSet->first();

            if($store->hasTranslation($request->input('locale'))) {
                $store->deleteTranslations($request->input('locale'));
            }

            $request->translationId = 'storetrad-' . Str::uuid();

            $store->translateOrNew($request->input('locale'))->fill(['id' => $request->translationId])->description = $request->input('description');

            $store->save();

            DB::commit();

            return response()->json($store->translate($request->input('locale'))->fresh());
        }
        catch(ModelNotFoundException $e) {
            return response()->json($e->getMessage(), 404);
        }
        catch(ValidationException $e) {
            return response()->json($e->response->original, 409);
        }
        catch(Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete  (
     *      path="/api/stores/{id}/translations/{translationId}",
     *      operationId="removeTranslation",
     *      tags={"Stores"},
     *      summary="Delete a store translation",
     *      description="Soft delete a store translation",
     *      @OA\Parameter(
     *          name="id",
     *          description="Account id",
     *          required=true,
     *          in="path",
     *      ),
     *     @OA\Parameter(
     *          name="locale",
     *          description="Locale",
     *          required=true,
     *          in="path",
     *      ),
     *      @OA\Response(response=200, description="Account deleted"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function removeTranslation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'locale' => 'required|string'
            ]);

            DB::beginTransaction();

            $resultSet = Store::where('stores.id', $request->id);

            $store = $resultSet->first();
            if (empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }


            $translationDeleted = $store->translate($request->input('locale'));

            if ($store->hasTranslation($request->input('locale'))) {
                $store->deleteTranslations($request->input('locale'));
            } else {
                throw new ModelNotFoundException('Translation not found.', 404);
            }

            $store->save();

            DB::commit();

            return response()->json($translationDeleted->fresh());
        } catch (ModelNotFoundException $e) {
            return response()->json($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return response()->json($e->response->original, 409);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
