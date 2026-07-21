@php
    $avaliadas = collect($detalhamento ?? [])->filter(fn ($l) => $l['avaliado'] ?? false);
@endphp
@if($avaliadas->isEmpty())
    <div class="muted small" style="padding:4px 2px;">Score não avaliado nesta consulta (nenhuma fonte de risco retornou).</div>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="right" style="width:64px;">Peso efetivo</th>
                <th style="width:46%;">Subscore</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalhamento as $linha)
                @php
                    $pesoEfetivo = $linha['peso_efetivo_pct'] ?? (($linha['avaliado'] ?? false) ? ($linha['peso_pct'] ?? null) : null);
                    $pesoBase = $linha['peso_base_pct'] ?? $linha['peso_pct'] ?? null;
                @endphp
                <tr>
                    <td>{{ $linha['label'] }}</td>
                    <td class="right" style="white-space:nowrap;">
                        @if($pesoEfetivo !== null)
                            {{ number_format((float) $pesoEfetivo, 1, ',', '.') }}%
                            <div class="muted" style="font-size:6.5px;">base {{ number_format((float) $pesoBase, 1, ',', '.') }}%</div>
                        @else
                            <span class="muted small">fora</span>
                        @endif
                    </td>
                    <td>
                        @if($linha['avaliado'])
                            <table style="width:100%;"><tr>
                                <td style="padding:0;">
                                    {{-- Barra = intensidade do estado (mesma fórmula da partial web):
                                         regular (score 0) enche de verde; irregular enche PELO RISCO
                                         (baixada 100 = barra cheia vermelha). Fórmula anterior (100 − score)
                                         deixava o pior caso com barra vazia — lia-se como "sem dado". --}}
                                    <div style="background:#f3f4f6;height:8px;width:100%;">
                                        <div style="background-color:{{ $linha['hex'] }};height:8px;width:{{ ((int) $linha['score']) === 0 ? 100 : max(0, min(100, (int) $linha['score'])) }}%;"></div>
                                    </div>
                                </td>
                                <td style="padding:0 0 0 6px;width:26px;white-space:nowrap;text-align:right;color:{{ $linha['hex'] }};font-weight:bold;">{{ $linha['score'] }}</td>
                            </tr></table>
                        @else
                            <span class="muted small">Não avaliado</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
