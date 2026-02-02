# Changelog

Todas as mudanças notáveis do projeto FiscalDock serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [1.3.13] - 2026-02-02

### Adicionado
- **Histórico de Consultas em Lote** na página do participante (`participante.blade.php`)
  - Nova seção mostrando os lotes em que o participante foi consultado
  - Exibe ID do lote, data, plano utilizado e status
  - Link para detalhes do lote no histórico de consultas

- **Link para o Lote** no card "Dados da Ultima Consulta"
  - Mostra "Lote #X" ao lado da data de consulta com link clicável

### Melhorado
- **Geocodificação do Mapa** com nova estratégia dual
  - Primário: BrasilAPI (`/api/cep/v2/{cep}`) - mais confiável para CEPs brasileiros
  - Fallback: Nominatim/OSM quando BrasilAPI não retorna coordenadas
  - Melhor tratamento de erros e mensagens mais claras

### Arquivos Modificados
- `app/Http/Controllers/Dashboard/MonitoramentoController.php`
  - Adicionado import do model `ConsultaLote`
  - Adicionada busca dos lotes que incluem o participante via `whereHas('resultados')`
  - Nova variável `$lotesDoParticipante` passada para a view

- `resources/views/autenticado/monitoramento/participante.blade.php`
  - Adicionado `data-cep` no container do mapa
  - Nova seção "Consultas em Lote" após o histórico de consultas avulsas
  - Link para o lote no header do card de última consulta
  - JavaScript reescrito para geocodificação com BrasilAPI + Nominatim

---

## [1.3.12] e anteriores

Versões anteriores não documentadas neste changelog.

---

## Links

- [Repositório](https://github.com/felipe-daige/hub_contabil)
- [Docker Hub](https://hub.docker.com/r/felipedaige/fiscaldock)
