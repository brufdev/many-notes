<?php

declare(strict_types=1);

use App\Models\Tag;

it('has valid schema', function (): void {
    $tag = Tag::factory()->create()->refresh();

    expect(array_keys($tag->toArray()))
        ->toBe([
            'id',
            'name',
            'created_at',
            'updated_at',
        ]);
});
