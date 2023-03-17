<?php

use App\Models\ShoppingList;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('needs authorization', function () {
    $this
        ->getJson('/api/shopping-list')
        ->assertStatus(401)
        ->assertExactJson([
            'message' => 'Unauthenticated.'
        ]);
});

it('can get shopping lists', function () {
    ShoppingList::factory(10)->create();

    $this
        ->actingAs($this->user)
        ->getJson('/api/shopping-list')
        ->assertStatus(200)
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'created_at', 'name', 'items', 'item_count']
            ]
        ]);
});

it('can create a shopping list', function () {
    $this
        ->actingAs($this->user)
        ->postJson('/api/shopping-list', [
            'name' => 'New List',
            'items' => [
                [
                    'name' => 'item 1',
                    'is_bought' => false,
                ]
            ]
        ])
        ->assertStatus(201)
        ->assertJson([
            'data' => [
                'created_at' => now()->format('d/m/Y'),
                'name' => 'New List',
                'items' => [
                    [
                        'name' => 'item 1',
                        'is_bought' => false,
                    ]
                ],
                'item_count' => 1,
            ]
        ]);

    $this->assertDatabaseHas(ShoppingList::class, [
        'name' => 'New List',
    ]);
});

it('can create a shopping list with no items', function () {
    $this
        ->actingAs($this->user)
        ->postJson('/api/shopping-list', [
            'name' => 'New List',
        ])
        ->assertStatus(201)
        ->assertJson([
            'data' => [
                'created_at' => now()->format('d/m/Y'),
                'name' => 'New List',
                'items' => [],
                'item_count' => 0,
            ]
        ]);

    $this->assertDatabaseHas(ShoppingList::class, [
        'name' => 'New List',
    ]);
});

it('will validate the name', function () {
    $this
        ->actingAs($this->user)
        ->postJson('/api/shopping-list', [
            'name' => '',
            'items' => [
                [
                    'name' => 'item 1',
                    'is_bought' => false,
                ]
            ]
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('will validate the items.name', function () {
    $this
        ->actingAs($this->user)
        ->postJson('/api/shopping-list', [
            'name' => 'Test name',
            'items' => [
                [
                    'name' => '',
                    'is_bought' => false,
                ]
            ]
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.name']);
});

it('will validate items.is_bought', function () {
    $this
        ->actingAs($this->user)
        ->postJson('/api/shopping-list', [
            'name' => 'Test name',
            'items' => [
                [
                    'name' => 'Item name',
                    'is_bought' => 'no-bool',
                ]
            ]
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.is_bought']);
});

it('can show a shopping list', function () {
    $shoppingList = ShoppingList::factory()->create();

    $this
        ->actingAs($this->user)
        ->getJson("/api/shopping-list/{$shoppingList->id}")
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'created_at' => now()->format('d/m/Y'),
                'name' => $shoppingList->name,
                'items' => $shoppingList->items,
                'item_count' => count($shoppingList->items),
            ]
        ]);
});

it('will not show a deleted shopping list', function () {
    $shoppingList = ShoppingList::factory()->create([
        'deleted_at' => now()->subDay(),
    ]);

    $this
        ->actingAs($this->user)
        ->getJson("/api/shopping-list/{$shoppingList->id}")
        ->assertStatus(404);
});

it('can update a shopping list', function () {
    $shoppingList = ShoppingList::factory()->create();

    $this
        ->actingAs($this->user)
        ->putJson("/api/shopping-list/{$shoppingList->id}", [
            'name' => 'Updated name',
            'items' => [
                [
                    'name' => 'new item',
                    'is_bought' => false,
                ],
            ],
        ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'created_at' => now()->format('d/m/Y'),
                'name' => 'Updated name',
                'items' => [
                    [
                        'name' => 'new item',
                        'is_bought' => false,
                    ]
                ],
                'item_count' => 1,
            ]
        ]);

    $this->assertDatabaseHas(ShoppingList::class, [
        'name' => 'Updated name',
    ]);

    $this->assertDatabaseMissing(ShoppingList::class, [
        'name' => $shoppingList->name,
    ]);
});

it('can not update a deleted shopping list', function () {
    $shoppingList = ShoppingList::factory()->create([
        'deleted_at' => now()->subDay(),
    ]);

    $this
        ->actingAs($this->user)
        ->putJson("/api/shopping-list/{$shoppingList->id}", [
            'name' => 'Updated name',
            'items' => [
                [
                    'name' => 'new item',
                    'is_bought' => false,
                ],
            ],
        ])
        ->assertStatus(404);

    $this->assertDatabaseMissing(ShoppingList::class, [
        'name' => 'Updated name',
    ]);
});

it('can delete a shopping list', function () {
    $shoppingList = ShoppingList::factory()->create();

    $this
        ->actingAs($this->user)
        ->deleteJson("/api/shopping-list/{$shoppingList->id}")
        ->assertNoContent(204);

    $this->assertSoftDeleted($shoppingList);
});

it('can not delete a deleted shopping list', function () {
    $shoppingList = ShoppingList::factory()->create([
        'deleted_at' => now()->subDay(),
    ]);

    $this
        ->actingAs($this->user)
        ->deleteJson("/api/shopping-list/{$shoppingList->id}")
        ->assertStatus(404);
});
