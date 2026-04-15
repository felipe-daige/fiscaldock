# Design System — DANFE Modernizado

Todas as views da área autenticada (`resources/views/autenticado/`) seguem o padrão visual "DANFE Modernizado" — inspirado no layout formal de documentos fiscais brasileiros (DANFE/NF-e). Público-alvo: contadores e analistas fiscais que esperam uma interface sóbria e profissional.

**Regra geral:** NUNCA usar classes Tailwind de cor para backgrounds de badges (ex: `bg-indigo-700`). Tailwind CSS v4 compila para `oklch()` via CSS variables, que pode não renderizar em todos os browsers. Usar **sempre `style="background-color: #hex"` inline** para cores de destaque.

## Página

| Elemento | Classe/Estilo |
|----------|--------------|
| Fundo da página | `bg-gray-100 min-h-screen` |
| Container | `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8` |
| Título da página | `text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide` |
| Subtítulo | `text-xs text-gray-500` |

## Blocos / Seções

| Elemento | Classe/Estilo |
|----------|--------------|
| Container do bloco | `bg-white rounded border border-gray-300 overflow-hidden` |
| Header do bloco | `bg-gray-50 px-4 py-2 border-b border-gray-200` |
| Label do header | `text-[10px] font-semibold text-gray-500 uppercase tracking-widest` |
| Contagem (badge no header) | `text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded` |

## KPIs (Resumo Fiscal)

| Elemento | Classe/Estilo |
|----------|--------------|
| Grid | `grid grid-cols-2 lg:grid-cols-N divide-x divide-gray-200` |
| Label do KPI | `text-[10px] font-semibold text-gray-400 uppercase tracking-wide` |
| Valor do KPI | `text-lg font-bold text-gray-900` (NUNCA usar cores vibrantes) |
| Sub-valor (ex: R$) | `text-[11px] text-gray-500` |

## Filtros

| Elemento | Classe/Estilo |
|----------|--------------|
| Labels | `text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1` |
| Inputs/Selects | `border border-gray-300 rounded text-sm focus:ring-1 focus:ring-gray-400 focus:border-gray-400` |
| Botão primário | `bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium` |
| Botão secundário | `bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded` |

## Tabela Desktop

| Elemento | Classe/Estilo |
|----------|--------------|
| thead tr | `border-b border-gray-300` |
| th | `px-3 py-2.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50` |
| tbody | `divide-y divide-gray-100` |
| tr hover | `hover:bg-gray-50/50 transition-colors` |
| Texto principal | `text-sm text-gray-700` |
| Valor monetário | `text-sm font-semibold text-gray-900 text-right font-mono` |

## Badges — Cores Inline (hex obrigatório)

**IMPORTANTE:** Sempre usar `style="background-color: #hex"` + classe `text-white`. Nunca usar classes Tailwind de cor de fundo para badges.

```html
<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white"
      style="background-color: #HEX">LABEL</span>
```

| Categoria | Variante | Cor hex |
|-----------|----------|---------|
| **Origem** | EFD | `#4338ca` (indigo-700) |
| **Origem** | XML | `#0f766e` (teal-700) |
| **Tipo operação** | Entrada | `#047857` (emerald-700) |
| **Tipo operação** | Saída | `#d97706` (amber-600) |
| **Modelo** | NF-e, CT-e, etc. | `#374151` (gray-700) |
| **Status** | OK | `#047857` (emerald-700) |
| **Status** | Divergente | `#d97706` (amber-600) |
| **Status** | Sem Mov. / Inativo | `#9ca3af` (gray-400) |
| **Código** | NCM, CFOP | `#4338ca` (indigo-700) |
| **Quantidade** | Contagem numérica | `#374151` (gray-700) |

## Paginação

| Elemento | Classe/Estilo |
|----------|--------------|
| Container | `border-t border-gray-300 px-4 py-3` |
| Info "Mostrando X–Y de Z" | `text-[10px] text-gray-500 uppercase tracking-wide` |
| Botão normal | `px-3 py-1.5 text-[10px] text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50` |
| Botão ativo | `text-[10px] font-bold text-white rounded` + `style="background-color: #1f2937"` |
| Botão disabled | `text-[10px] text-gray-400 bg-gray-100 border border-gray-200 rounded` |

## Mobile Cards

| Elemento | Classe/Estilo |
|----------|--------------|
| Container | `divide-y divide-gray-100` dentro do bloco branco |
| Card | `px-4 py-3` |
| Labels | `text-[10px] text-gray-400 uppercase` |
| Badges | Mesmos inline hex do desktop |
| Links de ação | `text-xs text-gray-600 hover:text-gray-900 hover:underline` |

## Links e Interações

| Elemento | Classe/Estilo |
|----------|--------------|
| Link em tabela | `text-gray-900 hover:text-gray-600 hover:underline` |
| Link de navegação | `text-gray-600 hover:text-gray-900 hover:underline` |
| Expand button | `text-gray-400 hover:text-gray-700 transition-colors` |

## Gráficos (ApexCharts)

| Elemento | Valor |
|----------|-------|
| Cor de barras (série única) | `#374151` (gray-700) |
| Grid | `borderColor: '#e5e7eb'` (gray-200) |
| Donut (multi-categoria) | `['#374151','#047857','#d97706','#dc2626','#7c3aed','#0891b2','#ea580c','#65a30d','#db2777','#4f46e5']` |
| Labels de gráfico | `text-[10px] font-semibold text-gray-400 uppercase tracking-wide` |

## Alertas

| Elemento | Classe/Estilo |
|----------|--------------|
| Container | `bg-white rounded border border-gray-300 p-4` |
| Borda lateral colorida | `border-l-4` + cor contextual (danger: `border-l-red-500`, warning: `border-l-amber-500`, info: `border-l-blue-500`) |
| Texto | `text-sm text-gray-700` |

## Views já convertidas (referência)

- `autenticado/importacao/efd-nota.blade.php` — detalhe da nota fiscal
- `autenticado/notas-fiscais/index.blade.php` — listagem de notas
- `autenticado/catalogo/index.blade.php` — catálogo de produtos
- `autenticado/bi/index.blade.php` + `public/js/bi.js` — BI Fiscal dashboard
- `autenticado/dashboard/index.blade.php` — Dashboard principal
- `autenticado/validacao/index.blade.php` — Clearance Dashboard
- `autenticado/validacao/notas.blade.php` + `public/js/clearance-notas.js` — Clearance Verificar Notas
- `autenticado/validacao/buscar-nfe.blade.php` + `public/js/clearance-buscar-nfe.js` — Busca avulsa de DF-e
- `autenticado/resumo-fiscal/index.blade.php` — Resumo Fiscal

## Views pendentes de conversão

Todas as demais views em `resources/views/autenticado/` que ainda usam o estilo antigo (cards coloridos, `bg-blue-600`, `rounded-lg`, badges com `bg-*-100 text-*-700`).
