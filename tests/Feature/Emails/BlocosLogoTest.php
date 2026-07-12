<?php

use App\Support\Mail\Blocos;

// F4 — memo da logo por mensagem (WeakMap), sem colisão entre mensagens no worker.

/** Stub de MailMessage: cada embed devolve um CID único e conta as chamadas. */
class FakeMailMessageParaLogo
{
    public int $embeds = 0;

    public function embed(string $arquivo): string
    {
        $this->embeds++;

        return 'cid:fake-'.spl_object_id($this).'-'.$this->embeds;
    }
}

it('memoiza: a mesma mensagem embute a logo só 1 vez', function () {
    $msg = new FakeMailMessageParaLogo;

    $a = Blocos::logoSrc($msg);
    $b = Blocos::logoSrc($msg);

    expect($a)->toBe($b);
    expect($msg->embeds)->toBe(1); // não duplica o anexo no MIME
});

it('mensagens distintas recebem CIDs próprios (sem vazar anexo entre elas)', function () {
    $m1 = new FakeMailMessageParaLogo;
    $m2 = new FakeMailMessageParaLogo;

    $c1 = Blocos::logoSrc($m1);
    $c2 = Blocos::logoSrc($m2);

    expect($c1)->not->toBe($c2);
});

it('sem mensagem cai na URL pública da logo', function () {
    expect(Blocos::logoSrc(null))->toContain('binary_files/logo');
});
