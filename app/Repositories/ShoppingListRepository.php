<?php

namespace App\Repositories;

use App\Http\Resources\ShoppingListResource;
use App\Models\ShoppingList;

class ShoppingListRepository
{
    public function getShoppingLists()
    {
        return ShoppingListResource::collection(
            auth()->user()->shoppingLists()->select(['id', 'created_at', 'name', 'items'])
                ->get()
        );
    }

    public function createShoppingList(array $shoppingListDetails)
    {
        if (! isset($shoppingListDetails['items'])) {
            $shoppingListDetails['items'] = [];
        }

        return auth()->user()->shoppingLists()->create($shoppingListDetails);
    }

    public function updateShoppingList(ShoppingList $shoppingList, array $newShoppingListDetails)
    {
        $shoppingList->update($newShoppingListDetails + ['items' => []]);

        return $shoppingList;
    }

    public function deleteShoppingList(ShoppingList $shoppingList)
    {
        return $shoppingList->delete();
    }
}
