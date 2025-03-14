<?php

declare(strict_types=1);

use App\Actions\ResolveTwoPaths;

it('resolves two absolute paths', function (): void {
    $currentPath = '/Personal/Inbox/Note.md';
    $path = '/Blog/Articles/Article.md';

    expect(new ResolveTwoPaths()->handle($currentPath, $path))
        ->toBe('/Blog/Articles/Article.md');
});

it('resolves one absolute path and one relative path going through the root', function (): void {
    $currentPath = '/Personal/Inbox/Note.md';
    $path = '../../Blog/Articles/Article.md';

    expect(new ResolveTwoPaths()->handle($currentPath, $path))
        ->toBe('/Blog/Articles/Article.md');
});

it('resolves one absolute path and one relative path not going throught the root', function (): void {
    $currentPath = '/Personal/Inbox/Note.md';
    $path = '../Letters/Letter.md';

    expect(new ResolveTwoPaths()->handle($currentPath, $path))
        ->toBe('/Personal/Letters/Letter.md');
});

it('resolves paths with utf-8 characters', function (): void {
    $currentPath = '/';
    $path = '/β.md';

    expect(new ResolveTwoPaths()->handle($currentPath, $path))
        ->toBe('/β.md');
});
