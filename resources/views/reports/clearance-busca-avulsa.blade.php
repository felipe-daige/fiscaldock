@extends('reports.layout')

@php
    $d = $nota['detalhes'] ?? [];
    $isCte = ($nota['tipo_documento'] ?? '') === 'CTE';
    $semCert = (bool) ($d['consulta_sem_certificado'] ?? false);
@endphp

@section('titulo', 'Clearance — Documento '.($nota['nfe_id'] ?? ''))
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('clearance-busca-avulsa', $nota['nfe_id'] ?? '', $nota['situacao'] ?? ''))

@section('meta')
    <div>Chave: {{ $nota['nfe_id'] ?? '—' }}</div>
    <div>Consultado na SEFAZ em: {{ $nota['consultado_em'] ?? '—' }}</div>
@endsection

@section('conteudo')
    {{-- Veredito --}}
    <div class="secao">
        <div class="secao-header">Situação do documento na SEFAZ</div>
        <div class="secao-body">
            <table>
                <tr>
                    <td style="width:120px;">
                        <span class="badge" style="background-color: {{ $nota['situacao_hex'] ?? '#374151' }};">{{ $nota['situacao'] ?? 'INDETERMINADO' }}</span>
                    </td>
                    <td class="muted" style="font-size:8px;">
                        {{ $nota['tipo_documento'] ?? 'NFE' }}{{ !empty($nota['modelo']) ? ' · modelo '.$nota['modelo'] : '' }}
                        · nº {{ $nota['numero'] ?? '—' }}/{{ $nota['serie'] ?? '—' }}
                        · emissão {{ $nota['data_emissao'] ?? '—' }}
                        · valor {{ $nota['valor_total_label'] ?? '—' }}
                    </td>
                </tr>
            </table>
            @if($semCert)
                <div style="background-color:#eff6ff;border:1px solid #bfdbfe;padding:6px;margin-top:8px;">
                    <span style="color:#1e3a8a;font-size:8px;">Consulta pública (sem certificado digital): a SEFAZ oculta parte dos dados — contraparte mascarada, descrições de itens reduzidas e totais/tributos não informados.</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Documento --}}
    <div class="secao">
        <div class="secao-header">Dados da operação</div>
        <div class="secao-body">
            <table class="table">
                <tr><td class="muted" style="width:160px;">Chave de acesso</td><td class="mono">{{ $nota['nfe_id'] ?? '—' }}</td></tr>
                <tr><td class="muted">Natureza da operação</td><td>{{ $d['natureza_operacao'] ?? '—' }}</td></tr>
                @if(!$isCte)
                    <tr><td class="muted">Tipo de operação</td><td>{{ $d['tipo_operacao'] ?? '—' }}</td></tr>
                @else
                    <tr><td class="muted">Tipo de serviço / CFOP</td><td>{{ $d['tipo_servico'] ?? '—' }}{{ !empty($d['cfop']) ? ' · CFOP '.$d['cfop'] : '' }}</td></tr>
                    <tr><td class="muted">Modal / Trajeto</td><td>{{ $d['modal'] ?? '—' }}{{ !empty($d['trajeto']) ? ' · '.$d['trajeto'] : '' }}</td></tr>
                    <tr><td class="muted">Valor da carga / prestação</td><td>{{ $d['valor_carga_label'] ?? '—' }} / {{ $d['valor_prestacao_label'] ?? '—' }}</td></tr>
                @endif
                <tr><td class="muted">Cliente associado</td><td>{{ $nota['cliente_nome'] ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Partes --}}
    <div class="secao">
        <div class="secao-header">Partes do documento</div>
        <div class="secao-body">
            <table class="table">
                <tr>
                    <td class="muted" style="width:160px;">Emitente</td>
                    <td>{{ $d['emit']['nome'] ?? '—' }} <span class="mono muted">{{ $d['emit']['documento'] ?? '' }}</span>
                        <span class="muted small">{{ !empty($d['emit']['ie']) ? 'IE '.$d['emit']['ie'] : '' }} {{ $d['emit']['local'] ?? '' }}</span></td>
                </tr>
                @if($isCte)
                    @foreach($d['partes'] ?? [] as $parte)
                        <tr>
                            <td class="muted">{{ $parte['papel'] }}{!! !empty($parte['identificado_acervo']) ? ' <span class="small" style="color:#0e7490;">(identificado no acervo)</span>' : '' !!}</td>
                            <td>{{ $parte['nome'] ?? '—' }} <span class="mono muted">{{ $parte['documento'] ?? '' }}</span> <span class="muted small">{{ $parte['local'] ?? '' }}</span></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="muted">Destinatário{!! !empty($d['dest']['identificado_acervo']) ? ' <span class="small" style="color:#0e7490;">(identificado no acervo)</span>' : '' !!}</td>
                        <td>{{ $d['dest']['nome'] ?? '—' }} <span class="mono muted">{{ $d['dest']['documento'] ?? '' }}</span> <span class="muted small">{{ $d['dest']['local'] ?? '' }}</span></td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Eventos --}}
    @if(!empty($d['eventos_timeline']))
        <div class="secao">
            <div class="secao-header">Eventos na SEFAZ <span class="meta">linha do tempo</span></div>
            <div class="secao-body">
                <table class="table">
                    <thead>
                        <tr><th style="width:70px;">Situação</th><th style="width:80px;">Data</th><th>Evento</th><th style="width:100px;">Protocolo</th></tr>
                    </thead>
                    <tbody>
                        @foreach($d['eventos_timeline'] as $ev)
                            <tr>
                                <td><span class="badge" style="background-color: {{ $ev['hex'] ?? '#374151' }};">{{ $ev['label'] }}</span></td>
                                <td>{{ $ev['data_label'] ?? '—' }}</td>
                                <td>{{ $ev['descricao'] ?? ($ev['label'] ?? '—') }}</td>
                                <td class="mono">{{ $ev['protocolo'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Totais (NF-e) --}}
    @if(!$isCte && !empty($d['totais']))
        <div class="secao">
            <div class="secao-header">Totais informados pela SEFAZ</div>
            <div class="secao-body">
                <table class="table">
                    @foreach($d['totais'] as $t)
                        <tr><td class="muted" style="width:220px;">{{ $t['label'] }}</td><td class="right">{{ $t['valor'] }}</td></tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif

    {{-- Produtos (NF-e) / Componentes (CT-e) --}}
    @if($isCte && !empty($d['componentes']))
        <div class="secao">
            <div class="secao-header">Componentes da prestação</div>
            <div class="secao-body">
                <table class="table">
                    <thead><tr><th>Componente</th><th class="right" style="width:110px;">Valor</th></tr></thead>
                    <tbody>
                        @foreach($d['componentes'] as $c)
                            <tr><td>{{ $c['nome'] ?? '—' }}</td><td class="right">{{ $c['valor'] ?? '—' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!$isCte && !empty($d['produtos']))
        <div class="secao">
            <div class="secao-header">Produtos ({{ count($d['produtos']) }}) @if($semCert)<span class="meta">descrições reduzidas na consulta pública</span>@endif</div>
            <div class="secao-body">
                <table class="table">
                    <thead>
                        <tr><th>Descrição</th><th style="width:60px;">NCM</th><th style="width:45px;">CFOP</th><th class="right" style="width:65px;">Qtd</th><th class="right" style="width:80px;">Valor</th></tr>
                    </thead>
                    <tbody>
                        @foreach($d['produtos'] as $p)
                            <tr>
                                <td>{{ $p['descricao'] ?? '—' }}</td>
                                <td class="mono">{{ $p['ncm'] ?? '—' }}</td>
                                <td class="mono">{{ $p['cfop'] ?? '—' }}</td>
                                <td class="right">{{ $p['quantidade'] ?? '—' }}</td>
                                <td class="right">{{ $p['valor'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
