<?php

declare(strict_types=1);

use App\Http\Controllers\SitemapController;

covers(SitemapController::class);

test('robots.txt returns 200 with correct content type', function () {
    $response = $this->get('/robots.txt');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
});

test('robots.txt disallows authenticated areas', function () {
    $content = $this->get('/robots.txt')->getContent();

    expect($content)->toContain('Disallow: /dashboard');
    expect($content)->toContain('Disallow: /home');
    expect($content)->toContain('Disallow: /tallies');
    expect($content)->toContain('Disallow: /settings');
});

test('robots.txt references the sitemap', function () {
    $content = $this->get('/robots.txt')->getContent();

    expect($content)->toContain('Sitemap: https://tallypal.it/sitemap.xml');
});

test('sitemap.xml returns 200 with XML content type', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
});

test('sitemap.xml contains all four public URLs', function () {
    $content = $this->get('/sitemap.xml')->getContent();

    expect($content)->toContain('https://tallypal.it/');
    expect($content)->toContain('https://tallypal.it/login');
    expect($content)->toContain('https://tallypal.it/register');
    expect($content)->toContain('https://tallypal.it/forgot-password');
});

test('sitemap.xml is valid XML', function () {
    $content = $this->get('/sitemap.xml')->getContent();

    $xml = simplexml_load_string($content);

    expect($xml)->not->toBeFalse();
});
