<?php

use Illuminate\Support\Facades\Route;

it('não expõe os endpoints do enriquecimento avulso de IE', function () {
    expect(Route::has('app.clearance.sintegra.preview'))->toBeFalse()
        ->and(Route::has('app.clearance.sintegra.executar'))->toBeFalse()
        ->and(Route::has('app.clearance.sintegra.status'))->toBeFalse();
});

it('não mantém os gatilhos nem o executor do enriquecimento avulso de IE', function () {
    $frontend = file_get_contents(resource_path('views/autenticado/clearance/notas-resultado.blade.php'))
        .file_get_contents(resource_path('views/autenticado/clearance/partials/_conferencias.blade.php'));
    $controller = file_get_contents(app_path('Http/Controllers/Dashboard/ClearanceController.php'));

    expect($frontend)->not->toContain('Enriquecer IE')
        ->not->toContain('Consultar SINTEGRA')
        ->not->toContain('clearanceSintegra')
        ->not->toContain('app.clearance.sintegra')
        ->and($controller)->not->toContain('function sintegraPreview')
        ->not->toContain('function sintegraExecutar')
        ->not->toContain('function sintegraStatus')
        ->not->toContain("somenteFontes: ['sintegra']");
});
