<?php

namespace App\Support;

use App\Models\User;

/**
 * Registro estático de destinos da command palette (Ctrl+K).
 * Montado server-side: gates de admin e feature flags decididos AQUI —
 * nunca expor rota escondida pro front decidir.
 */
class PaletteRegistry
{
    /** @return list<array{label: string, href: string, grupo: string, keywords: list<string>}> */
    public static function build(?User $user): array
    {
        $itens = [
            ['label' => 'Dashboard', 'href' => '/app/dashboard', 'grupo' => 'Painel', 'keywords' => ['inicio', 'home', 'painel']],
            ['label' => 'Alertas', 'href' => '/app/alertas', 'grupo' => 'Painel', 'keywords' => ['avisos', 'notificacoes', 'prazos']],
            ['label' => 'Status dos serviços', 'href' => '/app/status', 'grupo' => 'Painel', 'keywords' => ['integracoes', 'disponibilidade']],
            ['label' => 'Notas Fiscais — Listagem', 'href' => '/app/notas', 'grupo' => 'Documentos', 'keywords' => ['nf', 'nfe', 'documentos']],
            ['label' => 'Notas Fiscais — Dashboard', 'href' => '/app/notas/dashboard', 'grupo' => 'Documentos', 'keywords' => ['graficos', 'cfop', 'tributario']],
            ['label' => 'Catálogo (EFD)', 'href' => '/app/catalogo', 'grupo' => 'Documentos', 'keywords' => ['itens', 'produtos', 'ncm']],
            ['label' => 'Importar EFD', 'href' => '/app/importacao/efd', 'grupo' => 'Documentos', 'keywords' => ['sped', 'upload', 'icms', 'pis', 'cofins']],
            ['label' => 'Importar XML', 'href' => '/app/importacao/xml', 'grupo' => 'Documentos', 'keywords' => ['nfe', 'upload', 'zip']],
            ['label' => 'Histórico de importações', 'href' => '/app/importacao/historico', 'grupo' => 'Documentos', 'keywords' => ['importacao', 'arquivos']],
            ['label' => 'Meus Arquivos', 'href' => '/app/arquivos', 'grupo' => 'Documentos', 'keywords' => ['upload', 'download', 'comprovantes', 'armazenamento', 'espaco']],
            ['label' => 'Clearance — Dashboard', 'href' => '/app/clearance/dashboard', 'grupo' => 'Inteligência', 'keywords' => ['sefaz', 'kpi']],
            ['label' => 'Clearance — Verificar Notas', 'href' => '/app/clearance/notas', 'grupo' => 'Inteligência', 'keywords' => ['validar', 'sefaz', 'situacao']],
            ['label' => 'BI Fiscal', 'href' => '/app/bi/dashboard', 'grupo' => 'Inteligência', 'keywords' => ['business intelligence', 'graficos', 'saldo']],
            ['label' => 'Catálogo de Itens (BI)', 'href' => '/app/bi/catalogo-itens', 'grupo' => 'Inteligência', 'keywords' => ['xml', 'efd', 'unificado', 'divergencia']],
            ['label' => 'Cruzamentos', 'href' => '/app/bi/cruzamentos', 'grupo' => 'Inteligência', 'keywords' => ['fornecedor', 'irregular', 'certidão']],
            ['label' => 'Resumo Fiscal', 'href' => '/app/resumo-fiscal', 'grupo' => 'Inteligência', 'keywords' => ['fechamento', 'apuracao', 'recolher']],
            ['label' => 'Nova consulta CNPJ', 'href' => '/app/consulta/painel', 'grupo' => 'Consultas', 'keywords' => ['cnd', 'certidao', 'compliance', 'consultar']],
            ['label' => 'Monitoramento', 'href' => '/app/monitoramento/painel', 'grupo' => 'Consultas', 'keywords' => ['monitorar', 'recorrente', 'automatico']],
            ['label' => 'Score Fiscal', 'href' => '/app/score-fiscal', 'grupo' => 'Consultas', 'keywords' => ['risco', 'nota', 'rating']],
            ['label' => 'Minha Empresa', 'href' => '/app/minha-empresa', 'grupo' => 'Cadastros', 'keywords' => ['empresa propria', 'cnpj']],
            ['label' => 'Clientes', 'href' => '/app/clientes', 'grupo' => 'Cadastros', 'keywords' => ['cadastro', 'carteira']],
            ['label' => 'Participantes', 'href' => '/app/participantes', 'grupo' => 'Cadastros', 'keywords' => ['fornecedores', 'contrapartes']],
            ['label' => 'Perfil', 'href' => '/app/perfil', 'grupo' => 'Conta', 'keywords' => ['senha', 'dados', 'email']],
            ['label' => 'Configurações', 'href' => '/app/configuracoes', 'grupo' => 'Conta', 'keywords' => ['preferencias']],
            ['label' => 'Privacidade', 'href' => '/app/privacidade', 'grupo' => 'Conta', 'keywords' => ['lgpd', 'dados', 'exclusao']],
            ['label' => 'Suporte', 'href' => '/app/suporte', 'grupo' => 'Conta', 'keywords' => ['ajuda', 'whatsapp', 'contato']],
            ['label' => 'Planos', 'href' => '/app/planos', 'grupo' => 'Financeiro', 'keywords' => ['assinatura', 'upgrade', 'precos']],
            ['label' => 'Saldo', 'href' => '/app/saldo', 'grupo' => 'Financeiro', 'keywords' => ['creditos', 'recarga', 'extrato']],
            ['label' => 'Faixa Comercial', 'href' => '/app/faixa-comercial', 'grupo' => 'Financeiro', 'keywords' => ['preco', 'desconto']],
        ];

        if (config('clearance.busca_avulsa.habilitada')) {
            $itens[] = ['label' => 'Clearance — Buscar Notas', 'href' => '/app/clearance/buscar', 'grupo' => 'Inteligência', 'keywords' => ['chave', 'avulsa']];
        }

        if ($user?->is_admin) {
            $itens[] = ['label' => 'Admin — Visão Geral', 'href' => '/app/admin', 'grupo' => 'Admin', 'keywords' => ['console', 'analytics']];
            $itens[] = ['label' => 'Admin — Usuários', 'href' => '/app/admin/usuarios', 'grupo' => 'Admin', 'keywords' => ['console', 'contas']];
            $itens[] = ['label' => 'Admin — Comercial', 'href' => '/app/admin/comercial', 'grupo' => 'Admin', 'keywords' => ['precos', 'parametros', 'override']];
        }

        return $itens;
    }
}
