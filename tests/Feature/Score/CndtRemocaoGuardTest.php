<?php

it('zero ref ATIVA de cndt/trabalhista em app/resources/config/database (exceto coluna morta)', function () {
    $saida = shell_exec(
        'cd '.base_path().' && grep -rniE "cndt|trabalhista" app resources config database public/js routes 2>/dev/null'
        .' | grep -viE "legado|score_trabalhista" || true'
    );

    $linhas = array_filter(explode("\n", trim((string) $saida)));
    expect($linhas)->toBe([], "refs ativas restantes:\n".implode("\n", $linhas));
});

it('coluna morta score_trabalhista só aparece como null/legado (nunca lida)', function () {
    $saida = shell_exec(
        'cd '.base_path().' && grep -rn "score_trabalhista" app resources public/js 2>/dev/null'
        .' | grep -v "score_trabalhista. => null" || true'
    );

    $linhas = array_filter(explode("\n", trim((string) $saida)));
    expect($linhas)->toBe([], "leituras da coluna morta:\n".implode("\n", $linhas));
});
