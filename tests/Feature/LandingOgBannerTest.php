<?php

use function Pest\Laravel\get;

it('publica o banner horizontal nos metadados sociais da landing', function () {
    $relativePath = 'binary_files/og/fiscaldock-radar-riscos-og-20260716-v1.png';
    $absolutePath = public_path($relativePath);
    $publicUrl = asset($relativePath);

    expect($absolutePath)->toBeFile();

    $dimensions = getimagesize($absolutePath);

    expect($dimensions)->not->toBeFalse()
        ->and($dimensions[0])->toBe(1200)
        ->and($dimensions[1])->toBe(630)
        ->and($dimensions['mime'])->toBe('image/png');

    foreach (['/', '/conteudos'] as $uri) {
        get($uri)
            ->assertOk()
            ->assertSee('property="og:image" content="'.$publicUrl.'"', false)
            ->assertSee('property="og:image:secure_url" content="'.$publicUrl.'"', false)
            ->assertSee('property="og:image:width" content="1200"', false)
            ->assertSee('property="og:image:height" content="630"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('name="twitter:image" content="'.$publicUrl.'"', false);
    }
});
