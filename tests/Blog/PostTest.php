<?php

use App\Blog\Post;
use Symfony\Component\Uid\Uuid;

test('post to string', function () {
    $post = new Post(
        Uuid::v4(),
        'Hello, World!',
        'https://example.com/hello-world',
        'This is a test description.',
        'This is a test post.',
        'John Doe',
        new DateTimeImmutable('2024-12-08 09:39:00'),
    );

    $expected = <<<'TEXT'
            Title: Hello, World!
            From: John Doe on 2024-12-08
            Description: This is a test description.
            This is a test post.
            TEXT;

    expect($post->toString())->toBe($expected);
});

test('post to array', function () {
    $id = Uuid::v4();
    $post = new Post(
        $id,
        'Hello, World!',
        'https://example.com/hello-world',
        'This is a test description.',
        'This is a test post.',
        'John Doe',
        new DateTimeImmutable('2024-12-08 09:39:00'),
    );

    $expected = [
        'id' => $id->toRfc4122(),
        'title' => 'Hello, World!',
        'link' => 'https://example.com/hello-world',
        'description' => 'This is a test description.',
        'content' => 'This is a test post.',
        'author' => 'John Doe',
        'date' => '2024-12-08',
    ];

    expect($post->toArray())->toBe($expected);
});
