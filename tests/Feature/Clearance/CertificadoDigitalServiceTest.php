<?php

use App\Models\Cliente;
use App\Models\User;
use App\Services\Clearance\CertificadoDigitalService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

if (! function_exists('gerarPfx')) {
    function gerarPfx(string $senha, string $cnpj = '00000000000191', int $dias = 365): string
    {
        $pkey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $csr = openssl_csr_new(['commonName' => "EMPRESA TESTE:{$cnpj}"], $pkey, ['digest_alg' => 'sha256']);
        $x509 = openssl_csr_sign($csr, null, $pkey, $dias, ['digest_alg' => 'sha256']);
        $pfx = '';
        openssl_pkcs12_export($x509, $pfx, $pkey, $senha);

        return $pfx;
    }
}

if (! function_exists('certUploadFile')) {
    function certUploadFile(string $pfx): \Illuminate\Http\UploadedFile
    {
        return \Illuminate\Http\UploadedFile::fake()->createWithContent('cert.pfx', $pfx);
    }
}

function certCliente(): Cliente
{
    $u = User::factory()->create();

    return Cliente::create([
        'user_id' => $u->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Empresa Propria',
    ]);
}

it('armazena certificado válido: grava linha, arquivo no disco privado e senha cifrada', function () {
    Storage::fake('local');
    $cliente = certCliente();
    $pfx = gerarPfx('segredo123');

    $cert = app(CertificadoDigitalService::class)->validarEArmazenar(certUploadFile($pfx), 'segredo123', $cliente);

    expect($cert->cliente_id)->toBe($cliente->id);
    expect($cert->cnpj)->toBe('00000000000191');
    expect($cert->validade)->not->toBeNull();
    Storage::disk('local')->assertExists($cert->arquivo_path);
    expect($cert->senha_cifrada)->not->toBe('segredo123');
    expect(Crypt::decryptString($cert->senha_cifrada))->toBe('segredo123');
});

it('rejeita senha incorreta', function () {
    Storage::fake('local');
    $cliente = certCliente();
    $pfx = gerarPfx('certa');

    expect(fn () => app(CertificadoDigitalService::class)->validarEArmazenar(certUploadFile($pfx), 'errada', $cliente))
        ->toThrow(ValidationException::class);
});

it('rejeita certificado expirado', function () {
    Storage::fake('local');
    $cliente = certCliente();
    $pfx = gerarPfx('s', '00000000000191', 365);
    \Carbon\Carbon::setTestNow(now()->addYears(2));

    expect(fn () => app(CertificadoDigitalService::class)->validarEArmazenar(certUploadFile($pfx), 's', $cliente))
        ->toThrow(ValidationException::class);

    \Carbon\Carbon::setTestNow();
});

it('rejeita certificado de outro CNPJ', function () {
    Storage::fake('local');
    $cliente = certCliente();
    $pfx = gerarPfx('s', '11222333000181');

    expect(fn () => app(CertificadoDigitalService::class)->validarEArmazenar(certUploadFile($pfx), 's', $cliente))
        ->toThrow(ValidationException::class);
});

it('remover apaga arquivo e linha; substituir não duplica', function () {
    Storage::fake('local');
    $cliente = certCliente();
    $svc = app(CertificadoDigitalService::class);

    $c1 = $svc->validarEArmazenar(certUploadFile(gerarPfx('s')), 's', $cliente);
    $c2 = $svc->validarEArmazenar(certUploadFile(gerarPfx('s')), 's', $cliente);
    expect(App\Models\CertificadoDigital::where('cliente_id', $cliente->id)->count())->toBe(1);
    Storage::disk('local')->assertMissing($c1->arquivo_path);

    $svc->remover($cliente);
    expect(App\Models\CertificadoDigital::where('cliente_id', $cliente->id)->count())->toBe(0);
    Storage::disk('local')->assertMissing($c2->arquivo_path);
});
