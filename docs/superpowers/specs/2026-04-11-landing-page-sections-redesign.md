# Landing Page — Redesign das Sections 6-12

**Data:** 2026-04-11
**Status:** Aprovado para implementacao
**Arquivo:** `resources/views/landing_page/paginas/inicio.blade.php`

## Contexto

As 4 primeiras sections da landing page (Hero, Fontes Oficiais, Como Funciona, Funcionalidades) seguem um design system coeso: paleta `#0b1f3a`/`#1e4fa0`/`#374151`, cards `rounded-2xl border border-gray-200`, tipografia `text-[10px]/[11px] uppercase tracking-wide` para labels, badges com hex inline, icones em caixas com borda sutil.

As sections 6-12 (Notebook, Para Quem E, Diferenciais, Seguranca LGPD, Depoimentos, FAQ, Contato) foram feitas antes e usam um estilo generico SaaS (`bg-blue-500`, `text-brand`, `bg-green-100`, blobs, emojis, `rounded-lg`). A inconsistencia visual prejudica a percepcao de qualidade e profissionalismo.

**Objetivo:** Alinhar todas as sections ao design system das primeiras 4, eliminando redundancias e melhorando a narrativa de conversao.

## Design System — Tokens de Referencia

(Extraidos de `resources/css/app.css` e das sections ja convertidas)

| Token | Valor |
|---|---|
| Primary dark | `#0b1f3a` |
| Primary blue | `#1e4fa0` |
| Gray 700 | `#374151` |
| Emerald 700 | `#047857` |
| Amber 600 | `#d97706` |
| Red 600 | `#dc2626` |
| Accent (CTA) | `#facc15` |
| Section label | `text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400` |
| Section title | `text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight` |
| Section subtitle | `text-sm sm:text-base text-gray-500 max-w-2xl mx-auto` |
| Card | `rounded-2xl border border-gray-200 p-6 lg:p-8` |
| Icon box | `w-14 h-14 rounded-xl flex items-center justify-center` + bg/border sutil |
| Tag de contexto | `text-[10px] font-medium uppercase tracking-wide text-gray-400` + `background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px` |
| CTA button | `.btn-cta` (definido em app.css — amarelo `#facc15`, texto `#0b1f3a`) |
| Badge | `style="background-color: #HEX"` + `text-white` (NUNCA classes Tailwind de cor) |

## Alteracoes

### 1. Hero — Integrar mockup do macbook

**O que:** Substituir `dashboard-mockup.jpg` por `macbook-mockup.png` na coluna direita do Hero.

**Detalhes:**
- Trocar `<img src="dashboard-mockup.jpg">` por `<img src="macbook-mockup.png">`
- Remover container `rounded-3xl border border-white/15 bg-white/5 p-2` — o macbook ja tem moldura propria
- Aplicar `drop-shadow-2xl` para profundidade
- Ajustar `max-h` se necessario (macbook tem aspect ratio diferente do screenshot)

**Remover:** Section `#notebook` inteira (linhas 962-995 do arquivo atual)

### 2. Para Quem E — Redesign no DS

**Layout:** `bg-white`, grid `grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6`, container `max-w-6xl`

**Header:**
```
label:    "Para quem e" (text-[11px] uppercase tracking-[0.18em] text-gray-400)
titulo:   "Feito para quem vive compliance fiscal" (text-2xl sm:text-3xl font-bold)
subtitulo: "Escritorios contabeis, empresas e contadores autonomos que querem proteger seus clientes contra riscos fiscais"
```

**Card (x3):**
```
container: rounded-2xl border border-gray-200 p-6 lg:p-8 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex flex-col
icon-box:  w-14 h-14 rounded-xl (Escritorios: bg #eef2f7/border #dce3ed, Empresas: bg #fef8ee/border #f5e6c8, Autonomos: bg #eefbf5/border #c6eed8)
titulo:    text-base font-bold text-gray-900
bullets:   text-sm text-gray-500 leading-relaxed, SVG checkmark colorido por card
footer:    tags de contexto (ex: "SPED · Multi-cliente · Alertas")
```

**Conteudo dos cards:** Manter o mesmo conteudo das 3 personas atuais (Escritorios Contabeis, Empresas, Contadores Autonomos) — so trocar emoji `✓` por SVG checkmark e alinhar visual.

### 3. Diferenciais — Formato Comparativo "Sem vs Com"

**Layout:** `bg-gray-50`, container `max-w-6xl`

**Header:**
```
label:    "Diferenciais" (text-[11px] uppercase tracking-[0.18em] text-gray-400)
titulo:   "O que muda com o FiscalDock" (text-2xl sm:text-3xl font-bold)
subtitulo: "Compare o dia a dia do seu escritorio sem e com a plataforma"
```

**Card comparativo:** Um unico card `rounded-2xl border border-gray-200 overflow-hidden`

**Coluna esquerda — "Sem FiscalDock":**
- Header: bg-gray-100, label com icone X vermelho
- 5 itens com icone X (`style="color: #dc2626"`) + texto `text-sm text-gray-600`

**Coluna direita — "Com FiscalDock":**
- Header: bg-white, label com icone check verde
- 5 itens com icone check (`style="color: #047857"`) + texto `text-sm text-gray-900 font-medium`

**Conteudo:**

| Sem FiscalDock | Com FiscalDock |
|---|---|
| Consulta CNPJ um por um no site da Receita | Importa o SPED e consulta todos de uma vez |
| Descobre fornecedor inapto so na auditoria | Alerta automatico assim que o status muda |
| Planilha manual de controle de CNDs | Dashboard com vencimentos e renovacao automatica |
| Revisa notas fiscais por amostragem | Verificacao em lote na SEFAZ por chave de acesso |
| Sem visao consolidada da operacao fiscal | BI Fiscal com faturamento, compras e tributos por cliente |

**Mobile:** Stacked (coluna esquerda em cima, direita embaixo). Cada coluna ocupa 100% da largura.

### 4. Seguranca LGPD — Banner Compacto

**Layout:** Mesmo estilo do banner de metricas existente.

**Estilo:**
```
section: py-8 sm:py-10
background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%)
grid: grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 text-center
```

**Cada item:**
```
icone: SVG w-6 h-6, color rgba(255,255,255,0.55)
label: text-sm font-semibold text-white
descricao: text-xs text-white/55, mt-1
```

**4 itens:**
1. Cadeado — "Controle de Acesso" — "Por perfil e empresa"
2. Escudo check — "Auditoria Completa" — "Registro de todas as acoes"
3. Cubo — "Dados Criptografados" — "Segregacao por cliente"
4. Escudo — "Conformidade LGPD" — "Boas praticas de tratamento"

### 5. Depoimentos — Ajuste Visual

**O que muda (apenas CSS, sem alterar conteudo):**

| De | Para |
|---|---|
| `ring-4 ring-purple-100` | remover ring |
| `ring-4 ring-blue-100` | remover ring |
| `ring-4 ring-green-100` | remover ring |
| `bg-gradient-to-r from-green-500 to-emerald-500` | `style="background-color: #047857"` |
| `bg-gradient-to-r from-green-500 to-teal-500` | `style="background-color: #047857"` |
| `text-blue-50` (aspas) | `text-gray-200` |
| `hover:border-blue-200` | `hover:border-gray-300` |
| `rounded-xl` | `rounded-2xl` |

**Header da section:** Alinhar ao DS:
```
label:    "Depoimentos" (text-[11px] uppercase tracking-[0.18em] text-gray-400)
titulo:   "O que nossos clientes dizem" (text-2xl sm:text-3xl font-bold text-gray-900 — sem text-brand)
subtitulo: "Resultados reais de escritorios contabeis..." (text-sm sm:text-base text-gray-500)
```

### 6. FAQ — Redesign Visual + Conteudo Novo

**Layout:** `bg-white`, container `max-w-3xl mx-auto`

**Header:** Alinhado ao DS (label + titulo + subtitulo, sem `text-brand`).

**Accordion:**
```
item:     rounded-xl border border-gray-200 mb-3 overflow-hidden
question: px-5 py-4 text-sm font-bold text-gray-900 hover:bg-gray-50/50
answer:   px-5 py-4 text-sm text-gray-600 border-t border-gray-100
seta:     w-4 h-4 text-gray-400, rotacao 180deg quando aberto
```

**5 perguntas novas:**

1. **"Preciso cancelar meu sistema contabil para usar o FiscalDock?"**
   Nao. O FiscalDock complementa Dominio, Alterdata, Contmatic e qualquer outro sistema. Voce continua usando normalmente — basta exportar o SPED do seu sistema e importar no FiscalDock. Sem integracao tecnica, sem configuracao.

2. **"Como funciona o sistema de creditos?"**
   Voce compra creditos e usa conforme a necessidade. Cada tipo de consulta (CNPJ, CND, verificacao de nota) consome uma quantidade especifica de creditos. Sem mensalidade fixa e sem surpresas — pague so pelo que usar.

3. **"Quais fontes de dados o FiscalDock consulta?"**
   Receita Federal, SEFAZ (todos os estados), PGFN, SINTEGRA e CEIS. Todos os dados vem de fontes oficiais do governo, consultados em tempo real. Nenhuma informacao e estimada ou inferida.

4. **"Meus dados e de meus clientes ficam seguros?"**
   Sim. Controle de acesso por perfil e empresa, segregacao completa entre clientes, criptografia em transito e repouso, e conformidade com LGPD. Cada usuario ve apenas os dados que precisa.

5. **"Posso testar antes de comprar creditos?"**
   Sim. Oferecemos acesso gratuito para voce conhecer a plataforma. Importe um SPED, veja os participantes extraidos, explore os dashboards — tudo sem custo. Quando decidir, compre creditos para ativar as consultas.

### 7. Contato → CTA Final

**Remover:** Formulario completo e card de informacoes de contato.

**Layout:** Section compacta com fundo escuro (gradiente do Hero).

```
section:  py-16 sm:py-20
background: linear-gradient(135deg, #0b1f3a 0%, #1e4fa0 100%)
container: max-w-3xl mx-auto text-center
```

**Conteudo:**
```
titulo:    "Proteja seus clientes contra riscos fiscais" (text-2xl sm:text-3xl font-bold text-white)
subtitulo: "Importe seu primeiro SPED gratuitamente e veja os resultados em minutos" (text-base text-white/70)
botoes:    flex justify-center gap-4 flex-wrap mt-8
  primario:   .btn-cta "Criar conta grátis" → /criar-conta
  secundario: botao outline branco "Falar com Especialista" → /agendar
rodape:    "Sem cartao de credito · Sem mensalidade" (text-xs text-white/40, mt-4)
```

**Botao secundario (outline branco):**
```
border: 2px solid rgba(255,255,255,0.3)
color: white
background: transparent
hover: background rgba(255,255,255,0.1)
rounded, text-sm font-semibold, px-5 py-3
```

## Ordem Final das Sections

```
1. Hero (+ macbook mockup)           existente + tweak
2. Fontes Oficiais (banner)          existente (sem alteracao)
3. Como Funciona                     existente (sem alteracao)
4. Funcionalidades (bento grid)      existente (sem alteracao)
5. Metricas (banner)                 existente (sem alteracao)
6. Para Quem E                       REDESIGN
7. Diferenciais (Sem vs Com)         REDESIGN
8. Seguranca LGPD (banner)           REDESIGN (compactar)
9. Depoimentos                       AJUSTE CSS
10. FAQ                              REDESIGN + conteudo novo
11. CTA Final                        SUBSTITUIR Contato
```

**Sections removidas:** Notebook/IA Fiscal (#notebook)

## Arquivos Impactados

| Arquivo | Alteracao |
|---|---|
| `resources/views/landing_page/paginas/inicio.blade.php` | Todas as alteracoes de sections |
| `public/js/inicio.js` | Verificar se ha JS especifico da section Notebook para remover |
| `public/js/faq.js` | Pode precisar de ajuste se classes do accordion mudarem |

## Verificacao

1. `npm run build` no host apos alteracoes
2. Abrir no browser: verificar todas as sections desktop + mobile (< 640px)
3. Verificar FAQ accordion funciona (abrir/fechar)
4. Verificar CTA links (`/criar-conta` e `/agendar`) funcionam
5. Verificar que nenhuma section usa classes Tailwind de cor para badges (`bg-*-100`, `bg-*-500`)
6. Lighthouse: score de performance nao deve degradar (mockup macbook vs dashboard)
7. Testar prefers-reduced-motion (animacoes desabilitadas)
