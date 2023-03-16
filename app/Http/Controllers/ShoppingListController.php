<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShoppingListRequest;
use App\Http\Resources\ShoppingListResource;
use App\Models\ShoppingList;
use App\Repositories\ShoppingListRepository;
use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ShoppingListRepository $shoppingListRepository)
    {
        return $shoppingListRepository->getShoppingLists();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ShoppingListRequest $request, ShoppingListRepository $shoppingListRepository)
    {
        $shoppingList = $shoppingListRepository->createShoppingList($request->validated());

        return new ShoppingListResource($shoppingList);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShoppingList $shoppingList)
    {
        return new ShoppingListResource($shoppingList);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ShoppingListRequest $request, ShoppingList $shoppingList, ShoppingListRepository $shoppingListRepository)
    {
        $shoppingList = $shoppingListRepository->updateShoppingList($shoppingList, $request->validated());

        return new ShoppingListResource($shoppingList);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShoppingList $shoppingList, ShoppingListRepository $shoppingListRepository)
    {
        $shoppingListRepository->deleteShoppingList($shoppingList);
    }
}
