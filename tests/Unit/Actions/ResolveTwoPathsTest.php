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

it('keeps plus signs', function (): void {
    expect(new ResolveTwoPaths()->handle('/Notes/Plan.md', 'C++.md'))
        ->toBe('/Notes/C++.md');
});

it('decodes the path after resolving it', function (string $path, string $expected): void {
    expect(new ResolveTwoPaths()->handle('/Folder/Note.md', $path))->toBe($expected);
})->with([
    'encoded space' => ['My%20Note.md', '/Folder/My Note.md'],
    'encoded hash' => ['C%23%20notes.md', '/Folder/C# notes.md'],
    'encoded dots' => ['%2e%2e/%2e%2e/img.png', '/Folder/../../img.png'],
    'encoded slash' => ['..%2fimg.png', '/Folder/../img.png'],
]);
