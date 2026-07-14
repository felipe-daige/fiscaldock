<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\XmlImportacao;

/** Resolve a primeira importação vinculada ao cadastro do cliente para exibição no perfil. */
final class ClienteOrigem
{
    /**
     * @return array{label: string, hex: string, arquivo: ?string, url: ?string}
     */
    public static function dados(Cliente $cliente): array
    {
        $tipo = strtoupper(trim((string) $cliente->origem_tipo));

        if ($tipo === 'PROPRIO') {
            return self::semImportacao('Empresa própria', '#374151');
        }

        if ($tipo === 'MANUAL') {
            return self::semImportacao('Manual', '#6b7280');
        }

        $efd = EfdImportacao::query()
            ->where('user_id', $cliente->user_id)
            ->where('cliente_id', $cliente->id)
            ->oldest('created_at')
            ->oldest('id')
            ->first(['id', 'user_id', 'cliente_id', 'tipo_efd', 'filename', 'created_at']);

        $xml = XmlImportacao::query()
            ->where('user_id', $cliente->user_id)
            ->where('cliente_id', $cliente->id)
            ->oldest('created_at')
            ->oldest('id')
            ->first(['id', 'user_id', 'cliente_id', 'tipo_documento', 'filename', 'created_at']);

        if (str_starts_with($tipo, 'SPED') || str_starts_with($tipo, 'EFD')) {
            return $efd ? self::efd($efd) : self::semImportacao('EFD', '#4338ca');
        }

        if (in_array($tipo, ['XML', 'NFE', 'NFSE', 'CTE'], true)) {
            return $xml ? self::xml($xml) : self::semImportacao(self::labelXml($tipo), self::hexXml($tipo));
        }

        if ($efd && $xml) {
            return $efd->created_at->lte($xml->created_at) ? self::efd($efd) : self::xml($xml);
        }

        if ($efd) {
            return self::efd($efd);
        }

        if ($xml) {
            return self::xml($xml);
        }

        return self::semImportacao('Manual', '#6b7280');
    }

    /** @return array{label: string, hex: string, arquivo: ?string, url: ?string} */
    private static function efd(EfdImportacao $importacao): array
    {
        return [
            'label' => $importacao->tipo_efd ?: 'EFD',
            'hex' => $importacao->tipo_efd === 'EFD PIS/COFINS' ? '#7c3aed' : '#4338ca',
            'arquivo' => trim((string) $importacao->filename) ?: null,
            'url' => route('app.importacao.efd.detalhes', $importacao->id, false),
        ];
    }

    /** @return array{label: string, hex: string, arquivo: ?string, url: ?string} */
    private static function xml(XmlImportacao $importacao): array
    {
        $tipo = strtoupper((string) $importacao->tipo_documento);

        return [
            'label' => self::labelXml($tipo),
            'hex' => self::hexXml($tipo),
            'arquivo' => trim((string) $importacao->filename) ?: null,
            'url' => route('app.importacao.xml.detalhes', $importacao->id, false),
        ];
    }

    private static function labelXml(string $tipo): string
    {
        return match ($tipo) {
            'NFSE' => 'XML NFS-e',
            'CTE' => 'XML CT-e',
            default => 'XML NF-e',
        };
    }

    private static function hexXml(string $tipo): string
    {
        return match ($tipo) {
            'NFSE' => '#0891b2',
            'CTE' => '#0369a1',
            default => '#0f766e',
        };
    }

    /** @return array{label: string, hex: string, arquivo: null, url: null} */
    private static function semImportacao(string $label, string $hex): array
    {
        return ['label' => $label, 'hex' => $hex, 'arquivo' => null, 'url' => null];
    }
}
