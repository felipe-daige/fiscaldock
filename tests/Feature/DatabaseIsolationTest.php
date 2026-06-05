<?php

// Guarda permanente: garante que a suíte NUNCA roda contra o banco de produção.
// O container de prod define DB_DATABASE=fiscaldock como env real; phpunit.xml usa
// <server force="true"> p/ forçar fiscaldock_test. Se este teste falhar, NÃO rode a
// suíte (risco de wipe via RefreshDatabase). Ver memory feedback_phpunit_server_vars.
it('nunca conecta no banco de produção durante testes', function () {
    expect(config('app.env'))->toBe('testing');
    expect(\Illuminate\Support\Facades\DB::connection()->getDatabaseName())->toBe('fiscaldock_test');
    expect(\Illuminate\Support\Facades\DB::connection()->getDatabaseName())->not->toBe('fiscaldock');
});
