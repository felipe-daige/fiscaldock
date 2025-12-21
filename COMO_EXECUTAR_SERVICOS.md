# Como Executar os Serviços

Este documento explica como executar os serviços: `CsvParserService`, `XmlParserService`, `XmlClassificationService` e `RegimeTributarioService`.

## 1. Via Rotas HTTP/API

### XmlParserService e XmlClassificationService

**Upload de XMLs:**
```bash
POST /api/xml/upload
Content-Type: multipart/form-data

# Enviar arquivo(s) XML no campo 'xmls[]'
```

**Processar documentos pendentes:**
```bash
POST /api/xml/processar
Content-Type: application/json

{
  "documento_ids": [1, 2, 3]  # Opcional: IDs específicos, ou vazio para processar todos pendentes
}
```

**Listar documentos:**
```bash
GET /api/xml/documentos?status=pendente&cnpj_emitente=12345678000190
```

**Consultar regime tributário (via RegimeTributarioService):**
- O serviço é executado automaticamente durante a classificação de XMLs

### CsvParserService

Usado internamente pelo `SpedUploadService` ao processar arquivos SPED.

## 2. Via Tinker (Console Interativo)

Execute o Tinker:
```bash
php artisan tinker
```

### Exemplos de uso:

#### CsvParserService
```php
$csvParser = app(\App\Services\CsvParserService::class);
$csv = "Nome;Idade;Email\nJoão;30;joao@email.com\nMaria;25;maria@email.com";
$resultado = $csvParser->parse($csv);
// Retorna: ['headers' => [...], 'rows' => [...]]
```

#### XmlParserService
```php
$xmlParser = app(\App\Services\XmlParserService::class);
$xmlContent = file_get_contents('/caminho/para/arquivo.xml');
$dados = $xmlParser->extrairDados($xmlContent);
// Retorna array com dados extraídos do XML
```

#### RegimeTributarioService
```php
$regimeService = app(\App\Services\RegimeTributarioService::class);
$regime = $regimeService->consultarRegimeTributario('12345678000190');
// Retorna: 'simples_nacional', 'lucro_real', 'lucro_presumido', 'mei' ou null

// Atualizar manualmente
$regimeService->atualizarRegimeTributario('12345678000190', 'simples_nacional');
```

#### XmlClassificationService
```php
$classificationService = app(\App\Services\XmlClassificationService::class);
$documento = \App\Models\XmlDocumento::find(1);
$sugestao = $classificationService->classificar($documento);
// Retorna array com sugestões de classificação
```

## 3. Via Comandos Artisan

Execute os comandos criados:
```bash
# Processar XMLs pendentes
php artisan xml:processar

# Consultar regime tributário de um CNPJ
php artisan regime:consultar 12345678000190

# Atualizar regime tributário manualmente
php artisan regime:atualizar 12345678000190 simples_nacional

# Parsear CSV
php artisan csv:parse /caminho/arquivo.csv
```

## 4. Via Código (Em Controllers, Jobs, etc.)

Os serviços são injetados automaticamente pelo Laravel:

```php
use App\Services\XmlParserService;
use App\Services\RegimeTributarioService;

class MeuController extends Controller
{
    public function __construct(
        private XmlParserService $xmlParser,
        private RegimeTributarioService $regimeService
    ) {}
    
    public function processar()
    {
        $dados = $this->xmlParser->extrairDados($xmlContent);
        $regime = $this->regimeService->consultarRegimeTributario($cnpj);
    }
}
```

## Fluxo Completo de Processamento de XML

1. **Upload**: `POST /api/xml/upload` - Envia XMLs
2. **Processar**: `POST /api/xml/processar` - Classifica e cria sugestões
3. **Aceitar/Ajustar**: `POST /api/xml/aceitar` ou `POST /api/xml/ajustar` - Aprova sugestões

Durante o processamento:
- `XmlParserService` extrai dados do XML
- `RegimeTributarioService` consulta regime do emitente
- `XmlClassificationService` classifica e sugere lançamentos

