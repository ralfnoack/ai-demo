<?php

uses(Tests\TestCase::class);

uses(Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents::class);

test('index', function (): void {
    $client = static::createClient();
    $client->request('GET', '/');

    static::assertResponseIsSuccessful();
    static::assertSelectorTextContains('h1', 'Welcome to the Symfony AI Demo');
    static::assertSelectorCount(6, '.card');
});
/*
test('chats', function (string $path, string $expectedHeadline) {
    $client = static::createClient();
    $client->request('GET', $path);

    static::assertResponseIsSuccessful();
    static::assertSelectorTextSame('h4', $expectedHeadline);
    static::assertSelectorCount(1, '#chat-submit');
})->with('provideChats');
*/
/*
 * @return iterable<array{string, string}>
 */
dataset('provideChats', function () {
    yield 'Blog' => ['/blog', 'Retrieval Augmented Generation based on the Symfony blog'];
    yield 'YouTube' => ['/youtube', 'Chat about a YouTube Video'];
    yield 'Wikipedia' => ['/wikipedia', 'Wikipedia Research'];
});
