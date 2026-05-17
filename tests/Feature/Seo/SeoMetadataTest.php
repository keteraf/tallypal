<?php

declare(strict_types=1);

test('welcome page returns 200 and contains required base SEO tags', function () {
    $response = $this->get('/');

    $response->assertStatus(200);

    $content = $response->getContent();

    // Title rendered by Blade (inertia attribute)
    expect($content)->toContain('<title');

    // Base description from Blade
    expect($content)->toContain('name="description"');
    expect($content)->toContain('Tallypal');

    // Canonical from Blade
    expect($content)->toContain('rel="canonical"');

    // OG tags from Blade
    expect($content)->toContain('property="og:image"');
    expect($content)->toContain('og/default.png');

    // OG site_name from Blade
    expect($content)->toContain('property="og:site_name"');
});

test('login page returns 200 and contains required SEO meta tags', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);

    $content = $response->getContent();

    expect($content)->toContain('<title');
    expect($content)->toContain('name="description"');
    expect($content)->toContain('rel="canonical"');
    expect($content)->toContain('property="og:image"');
});

test('register page returns 200 and contains required SEO meta tags', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);

    $content = $response->getContent();

    expect($content)->toContain('<title');
    expect($content)->toContain('name="description"');
    expect($content)->toContain('rel="canonical"');
    expect($content)->toContain('property="og:image"');
});

test('welcome page contains JSON-LD organization schema', function () {
    $response = $this->get('/');

    $response->assertStatus(200);

    $content = $response->getContent();

    expect($content)->toContain('application/ld+json');
    expect($content)->toContain('"Organization"');
    expect($content)->toContain('"WebSite"');
});

test('welcome page contains Tallypal brand reference', function () {
    $response = $this->get('/');

    $response->assertStatus(200);

    $content = $response->getContent();

    expect($content)->toContain('Tallypal');
});

test('theme-color meta is present on all public pages', function () {
    foreach (['/login', '/register', '/'] as $url) {
        $content = $this->get($url)->getContent();

        expect($content)->toContain('name="theme-color"');
    }
});
