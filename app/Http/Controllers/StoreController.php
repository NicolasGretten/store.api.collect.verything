<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Traits\FiltersTrait;
use App\Traits\IdTrait;
use App\Traits\JwtTrait;
use App\Traits\LocaleTrait;
use App\Traits\PaginationTrait;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    /**
     * @OA\Info(title="Store API Collect&Verything", version="0.1")
     */
    use JWTTrait, FiltersTrait, PaginationTrait, IdTrait, LocaleTrait;

    /**
     * @OA\Get(
     *      path="/api/stores/{id}",
     *      operationId="retrieve",
     *      tags={"Stores"},
     *      summary="Get store information",
     *      description="Returns store data",
     *      @OA\Parameter(name="id",description="Store id", required=true, in="path"),
     *      @OA\Parameter(name="locale",description="Locale", required=false, in="path"),
     *      @OA\Response(response=200, description="successful operation"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Account not found."),
     *      @OA\Response(response=409, description="Conflict"),
     *      @OA\Response(response=500, description="Servor Error"),
     * )
     */
    public function retrieve(Request $request): JsonResponse
    {
        try {

            $this->setLocale();

            $resultSet = Store::select('stores.*')->where('id', $request->id);

            $store = $resultSet->first();

            if(empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }

            return response()->json($store, 200);
        }
        catch(ValidationException | ModelNotFoundException | Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/stores",
     *      operationId="list",
     *      tags={"Stores"},
     *      summary="Get all stores information",
     *      description="Returns store data",
     *      @OA\Parameter(name="locale",description="Locale", required=false, in="path"),
     *      @OA\Response(response=200, description="successful operation"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=409, description="Conflict"),
     *      @OA\Response(response=500, description="Servor Error"),
     * )
     */
    public function list(Request $request): JsonResponse
    {
        try {
//            $this->validate($request, [
//                'limit'         => 'int|required_with:page',
//                'page'          => 'int|required_with:limit',
//                'items_id'      => 'json'
//            ]);

            $this->setLocale();

            $resultSet = Store::select('stores.*');

            $this->filter($resultSet, ['date', 'itemsId'])->paginate($resultSet);

            return response()->json($resultSet->get(), 200, ['pagination' => $this->pagination]);
        }
        catch(ValidationException | ModelNotFoundException | Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/stores",
     *      operationId="create",
     *      tags={"Stores"},
     *      summary="Post a new store",
     *      description="Create a new store",
     *      @OA\Parameter(name="account_id", description="Account Id", required=true, in="query"),
     *      @OA\Parameter(name="name", description="Store name", required=true, in="query"),
     *      @OA\Parameter(name="business_name", description="Store business name", required=true, in="query"),
     *      @OA\Parameter(name="address_id", description="Address Id", required=true, in="query"),
     *      @OA\Parameter(name="phone", description="Store phone", required=true, in="query"),
     *      @OA\Parameter(name="email", description="Store email", required=true, in="query"),
     *      @OA\Parameter(name="type", description="Store type", required=true, in="query"),
     *      @OA\Parameter(name="openings", description="Store openings", required=true, in="query"),
     *      @OA\Parameter(name="primary_color", description="Store primary color", required=true, in="query"),
     *      @OA\Parameter(name="secondary_color", description="Store secondary color", required=true, in="query"),
     *      @OA\Parameter(name="logo", description="Store logo", required=true, in="query"),
     *      @OA\Parameter(name="locale", description="Locale", required=true, in="query"),
     *      @OA\Parameter(name="description", description="Description", required=true, in="query"),
     *      @OA\Response(response=201,description="Account created"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'account_id'         => 'required|string',
                'name'              => 'required|string',
                'business_name'      => 'required|string',
                'address_id'         => 'required|string',
                'phone'             => 'required|string',
                'email'             => 'required|string',
                'type'              => 'required|string',
                'openings'          => 'required|string',
                'primary_color'      => 'required|string',
                'secondary_color'    => 'required|string',
                'logo'              => 'required|string',
                'locale'            => 'required|string',
                'description'       => 'required|string',
            ]);

            DB::beginTransaction();

            $request->translationId = 'storetrad-' . Str::uuid();

            $store = new Store();
            $store->id              = $this->generateId('store', $store);
            $store->account_id       = $request->input('account_id');
            $store->name            = $request->input('name');
            $store->business_name    = $request->input('business_name');
            $store->address_id       = $request->input('address_id');
            $store->phone           = $request->input('phone');
            $store->email           = $request->input('email');
            $store->type            = $request->input('type');
            $store->openings        = $request->input('openings');
            $store->primary_color    = $request->input('primary_color');
            $store->secondary_color  = $request->input('secondary_color');
            $store->logo            = $request->input('logo');
            $store->translateOrNew($request->input('locale'))
                ->fill(['id' => $request->translationId])
                ->description = $request->input('description');

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
     * @OA\Patch (
     *      path="/api/stores/{id}",
     *      operationId="update",
     *      tags={"Stores"},
     *      summary="Patch a store",
     *      description="Update an store",
     *      @OA\Parameter(name="id",description="Store id", required=true, in="path"),
     *      @OA\Parameter(name="account_id", description="Account Id", required=false, in="query"),
     *      @OA\Parameter(name="name", description="Store name", required=false, in="query"),
     *      @OA\Parameter(name="business_name", description="Store business Name", required=false, in="query"),
     *      @OA\Parameter(name="address_id", description="Address Id", required=false, in="query"),
     *      @OA\Parameter(name="phone", description="Store phone", required=false, in="query"),
     *      @OA\Parameter(name="email", description="Store email", required=false, in="query"),
     *      @OA\Parameter(name="type", description="Store type", required=false, in="query"),
     *      @OA\Parameter(name="openings", description="Store openings", required=false, in="query"),
     *      @OA\Parameter(name="primary_color", description="Store primary color", required=false, in="query"),
     *      @OA\Parameter(name="secondary_color", description="Store secondary color", required=false, in="query"),
     *      @OA\Parameter(name="logo", description="Store logo", required=false, in="query"),
     *      @OA\Response(
     *          response=200,
     *          description="Account updated"
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'account_id'         => 'string',
                'name'              => 'string',
                'business_name'      => 'string',
                'address_id'         => 'string',
                'phone'             => 'string',
                'email'             => 'string',
                'type'              => 'string',
                'openings'          => 'string',
                'primary_color'      => 'string',
                'secondary_color'    => 'string',
                'logo'              => 'string',
            ]);

            DB::beginTransaction();

            $store = Store::select('stores.*')->where('id', $request->id)->first();

            if(empty($store)) {
                throw new ModelNotFoundException('Store not found.', 404);
            }

            $store->account_id       = $request->input('account_id', $store->getOriginal('account_id'));
            $store->name            = $request->input('name', $store->getOriginal('name'));
            $store->business_name    = $request->input('business_name', $store->getOriginal('business_name'));
            $store->address_id       = $request->input('address_id', $store->getOriginal('address_id'));
            $store->phone           = $request->input('phone', $store->getOriginal('phone'));
            $store->email           = $request->input('email', $store->getOriginal('email'));
            $store->type            = $request->input('type', $store->getOriginal('type'));
            $store->openings        = $request->input('openings', $store->getOriginal('openings'));
            $store->primary_color    = $request->input('primary_color', $store->getOriginal('primary_color'));
            $store->secondary_color  = $request->input('secondary_color', $store->getOriginal('secondary_color'));
            $store->logo            = $request->input('logo', $store->getOriginal('logo'));

            $store->save();

            DB::commit();

            return response()->json($store, 200);
        }
        catch(ModelNotFoundException | JsonEncodingException $e) {
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
     *      path="/api/stores/{id}",
     *      operationId="delete",
     *      tags={"Stores"},
     *      summary="Delete a store",
     *      description="Soft delete a store",
     *      @OA\Parameter(
     *          name="id",
     *          description="Store id",
     *          required=true,
     *          in="path",
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Account deleted"
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function delete(Request $request): JsonResponse
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
