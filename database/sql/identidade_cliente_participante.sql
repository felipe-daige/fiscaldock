-- Invariável de identidade FiscalDock, ESCOPADA POR MOVIMENTO:
--
--   Participante que é APENAS cadastro (nenhuma nota vinculada) e duplica um cliente do mesmo
--   usuário é consolidado no cliente — cliente prevalece. Era o caso da HIDRATOP: a mesma
--   empresa aparecia duas vezes nas telas sem nunca ter movimentado nada.
--
--   Participante COM movimento sobrevive, mesmo duplicando um cliente. Dois clientes do mesmo
--   usuário que negociam entre si aparecem um nas notas do outro, e isso é fato fiscal, não
--   duplicidade de cadastro. Nesses casos `efd_notas` carrega os DOIS vínculos: `participante_id`
--   (lido por todas as superfícies analíticas) e `contraparte_cliente_id` (marca aditiva de que
--   a contraparte também é cliente).
--
-- Este script é idempotente: consolida colisões preexistentes e repara notas que perderam o
-- participante_id na primeira versão da invariável (que apagava participante com movimento).

ALTER TABLE efd_notas
    ADD COLUMN IF NOT EXISTS contraparte_cliente_id BIGINT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'efd_notas_contraparte_cliente_id_foreign'
          AND conrelid = 'efd_notas'::regclass
    ) THEN
        ALTER TABLE efd_notas
            ADD CONSTRAINT efd_notas_contraparte_cliente_id_foreign
            FOREIGN KEY (contraparte_cliente_id)
            REFERENCES clientes(id)
            ON DELETE SET NULL;
    END IF;
END
$$;

CREATE INDEX IF NOT EXISTS efd_notas_contraparte_cliente_id_index
    ON efd_notas (contraparte_cliente_id);

CREATE OR REPLACE FUNCTION fiscaldock_documento_normalizado(valor TEXT)
RETURNS TEXT
LANGUAGE SQL
IMMUTABLE
PARALLEL SAFE
AS $$
    SELECT regexp_replace(COALESCE(valor, ''), '[^0-9]', '', 'g')
$$;

CREATE OR REPLACE FUNCTION fiscaldock_travar_identidade(
    p_user_id BIGINT,
    p_documento TEXT
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
    v_documento TEXT;
BEGIN
    v_documento := fiscaldock_documento_normalizado(p_documento);

    IF p_user_id IS NULL OR v_documento = '' THEN
        RETURN;
    END IF;

    PERFORM pg_advisory_xact_lock(
        hashtextextended(p_user_id::TEXT || ':' || v_documento, 734621)
    );
END
$$;

CREATE OR REPLACE FUNCTION fiscaldock_consolidar_participante_cliente(
    p_participante_id BIGINT,
    p_cliente_id BIGINT
)
RETURNS VOID
LANGUAGE plpgsql
AS $$
DECLARE
    v_user_participante BIGINT;
    v_user_cliente BIGINT;
    v_doc_participante TEXT;
    v_doc_cliente TEXT;
BEGIN
    SELECT user_id, fiscaldock_documento_normalizado(documento)
      INTO v_user_participante, v_doc_participante
      FROM participantes
     WHERE id = p_participante_id
     FOR UPDATE;

    IF NOT FOUND THEN
        RETURN;
    END IF;

    SELECT user_id, fiscaldock_documento_normalizado(documento)
      INTO v_user_cliente, v_doc_cliente
      FROM clientes
     WHERE id = p_cliente_id
     FOR UPDATE;

    IF NOT FOUND
       OR v_user_participante IS DISTINCT FROM v_user_cliente
       OR v_doc_participante = ''
       OR v_doc_participante IS DISTINCT FROM v_doc_cliente THEN
        RAISE EXCEPTION
            'Participante % e cliente % não representam a mesma identidade',
            p_participante_id,
            p_cliente_id
            USING ERRCODE = 'check_violation';
    END IF;

    PERFORM fiscaldock_travar_identidade(v_user_cliente, v_doc_cliente);

    -- REGRA DE MOVIMENTO: só consolida (= apaga) participante que é DUPLICATA DE CADASTRO.
    -- Participante com nota vinculada representa movimento real — dois clientes do mesmo
    -- usuário que negociam entre si aparecem um nas notas do outro, e isso é legítimo.
    -- Apagá-lo zerava efd_notas.participante_id e movia o vínculo pra contraparte_cliente_id,
    -- coluna que NENHUMA superfície analítica lê (BiService::getFichaParticipante e o
    -- dedupParticipanteSql filtram por participante_id): o movimento sumia das telas de
    -- participante sem erro nenhum. Nesse caso apenas CARIMBA o vínculo de cliente e mantém
    -- o participante vivo.
    IF EXISTS (SELECT 1 FROM efd_notas WHERE participante_id = p_participante_id)
       OR EXISTS (SELECT 1 FROM xml_notas WHERE emit_participante_id = p_participante_id)
       OR EXISTS (SELECT 1 FROM xml_notas WHERE dest_participante_id = p_participante_id) THEN
        UPDATE efd_notas
           SET contraparte_cliente_id = p_cliente_id
         WHERE participante_id = p_participante_id
           AND contraparte_cliente_id IS DISTINCT FROM p_cliente_id;

        RETURN;
    END IF;

    -- Quando os dois alvos foram consultados no mesmo lote, preserva uma linha única e
    -- mescla o JSON; os campos do resultado mais recente vencem.
    UPDATE consulta_resultados AS destino
       SET resultado_dados = CASE
               WHEN COALESCE(origem.consultado_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.consultado_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(destino.resultado_dados, '{}'::jsonb)
                        || COALESCE(origem.resultado_dados, '{}'::jsonb)
               ELSE COALESCE(origem.resultado_dados, '{}'::jsonb)
                    || COALESCE(destino.resultado_dados, '{}'::jsonb)
           END,
           status = CASE
               WHEN COALESCE(origem.consultado_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.consultado_em, destino.updated_at, destino.created_at)
                   THEN origem.status
               ELSE destino.status
           END,
           error_message = CASE
               WHEN COALESCE(origem.consultado_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.consultado_em, destino.updated_at, destino.created_at)
                   THEN origem.error_message
               ELSE destino.error_message
           END,
           consultado_em = GREATEST(destino.consultado_em, origem.consultado_em),
           updated_at = GREATEST(destino.updated_at, origem.updated_at)
      FROM consulta_resultados AS origem
     WHERE origem.participante_id = p_participante_id
       AND destino.cliente_id = p_cliente_id
       AND destino.consulta_lote_id = origem.consulta_lote_id;

    DELETE FROM consulta_resultados AS origem
     WHERE origem.participante_id = p_participante_id
       AND EXISTS (
           SELECT 1
             FROM consulta_resultados AS destino
            WHERE destino.cliente_id = p_cliente_id
              AND destino.consulta_lote_id = origem.consulta_lote_id
       );

    UPDATE consulta_resultados
       SET participante_id = NULL,
           cliente_id = p_cliente_id
     WHERE participante_id = p_participante_id;

    -- Score é snapshot, não histórico: preserva o snapshot mais recente e completa campos
    -- ausentes com o outro lado. O JSON acumulado mantém todas as fontes conhecidas.
    UPDATE participante_scores AS destino
       SET score_cadastral = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.score_cadastral, destino.score_cadastral)
               ELSE COALESCE(destino.score_cadastral, origem.score_cadastral)
           END,
           score_cnd_federal = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.score_cnd_federal, destino.score_cnd_federal)
               ELSE COALESCE(destino.score_cnd_federal, origem.score_cnd_federal)
           END,
           score_cnd_estadual = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.score_cnd_estadual, destino.score_cnd_estadual)
               ELSE COALESCE(destino.score_cnd_estadual, origem.score_cnd_estadual)
           END,
           score_fgts = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.score_fgts, destino.score_fgts)
               ELSE COALESCE(destino.score_fgts, origem.score_fgts)
           END,
           score_trabalhista = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.score_trabalhista, destino.score_trabalhista)
               ELSE COALESCE(destino.score_trabalhista, origem.score_trabalhista)
           END,
           score_credito_reforma = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.score_credito_reforma, destino.score_credito_reforma)
               ELSE COALESCE(destino.score_credito_reforma, origem.score_credito_reforma)
           END,
           score_total = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.score_total, destino.score_total)
               ELSE COALESCE(destino.score_total, origem.score_total)
           END,
           classificacao = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.classificacao, destino.classificacao)
               ELSE COALESCE(destino.classificacao, origem.classificacao)
           END,
           ultima_consulta_em = GREATEST(destino.ultima_consulta_em, origem.ultima_consulta_em),
           proxima_consulta_em = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(origem.proxima_consulta_em, destino.proxima_consulta_em)
               ELSE COALESCE(destino.proxima_consulta_em, origem.proxima_consulta_em)
           END,
           dados_consultados = CASE
               WHEN COALESCE(origem.ultima_consulta_em, origem.updated_at, origem.created_at)
                    > COALESCE(destino.ultima_consulta_em, destino.updated_at, destino.created_at)
                   THEN COALESCE(destino.dados_consultados, '{}'::jsonb)
                        || COALESCE(origem.dados_consultados, '{}'::jsonb)
               ELSE COALESCE(origem.dados_consultados, '{}'::jsonb)
                    || COALESCE(destino.dados_consultados, '{}'::jsonb)
           END,
           updated_at = GREATEST(destino.updated_at, origem.updated_at)
      FROM participante_scores AS origem
     WHERE origem.participante_id = p_participante_id
       AND destino.cliente_id = p_cliente_id;

    DELETE FROM participante_scores
     WHERE participante_id = p_participante_id
       AND EXISTS (
           SELECT 1 FROM participante_scores WHERE cliente_id = p_cliente_id
       );

    UPDATE participante_scores
       SET participante_id = NULL,
           cliente_id = p_cliente_id
     WHERE participante_id = p_participante_id;

    -- Certidão canônica: uma linha por documento/fonte. A emissão mais recente vence.
    UPDATE certidoes AS destino
       SET orgao = origem.orgao,
           status = origem.status,
           certidao_codigo = origem.certidao_codigo,
           emitida_em = origem.emitida_em,
           valida_ate = origem.valida_ate,
           validade_origem = origem.validade_origem,
           arquivo_path = origem.arquivo_path,
           consulta_lote_id = origem.consulta_lote_id,
           updated_at = origem.updated_at
      FROM certidoes AS origem
     WHERE origem.participante_id = p_participante_id
       AND destino.cliente_id = p_cliente_id
       AND destino.user_id = origem.user_id
       AND destino.alvo_documento = origem.alvo_documento
       AND destino.tipo = origem.tipo
       AND COALESCE(origem.updated_at, origem.created_at)
           > COALESCE(destino.updated_at, destino.created_at);

    DELETE FROM certidoes AS origem
     WHERE origem.participante_id = p_participante_id
       AND EXISTS (
           SELECT 1
             FROM certidoes AS destino
            WHERE destino.cliente_id = p_cliente_id
              AND destino.user_id = origem.user_id
              AND destino.alvo_documento = origem.alvo_documento
              AND destino.tipo = origem.tipo
       );

    UPDATE certidoes
       SET participante_id = NULL,
           cliente_id = p_cliente_id,
           alvo_tipo = 'cliente'
     WHERE participante_id = p_participante_id;

    UPDATE certidao_pedidos
       SET participante_id = NULL,
           cliente_id = p_cliente_id,
           alvo_tipo = 'cliente'
     WHERE participante_id = p_participante_id;

    -- Se já houver a mesma assinatura no Cliente, mantém a mais recentemente alterada.
    UPDATE monitoramento_assinaturas AS destino
       SET status = origem.status,
           frequencia_dias = origem.frequencia_dias,
           proxima_execucao_em = origem.proxima_execucao_em,
           ultima_execucao_em = origem.ultima_execucao_em,
           pausada_motivo = origem.pausada_motivo,
           fontes = origem.fontes,
           updated_at = origem.updated_at
      FROM monitoramento_assinaturas AS origem
     WHERE origem.participante_id = p_participante_id
       AND destino.cliente_id = p_cliente_id
       AND destino.plano_id IS NOT DISTINCT FROM origem.plano_id
       AND COALESCE(origem.updated_at, origem.created_at)
           > COALESCE(destino.updated_at, destino.created_at);

    DELETE FROM monitoramento_assinaturas AS origem
     WHERE origem.participante_id = p_participante_id
       AND EXISTS (
           SELECT 1
             FROM monitoramento_assinaturas AS destino
            WHERE destino.cliente_id = p_cliente_id
              AND destino.plano_id IS NOT DISTINCT FROM origem.plano_id
       );

    UPDATE monitoramento_assinaturas
       SET participante_id = NULL,
           cliente_id = p_cliente_id
     WHERE participante_id = p_participante_id;

    UPDATE monitoramento_consultas
       SET participante_id = NULL,
           cliente_id = p_cliente_id
     WHERE participante_id = p_participante_id;

    UPDATE alertas
       SET participante_id = NULL,
           cliente_id = p_cliente_id
     WHERE participante_id = p_participante_id;

    -- Em EFD, cliente_id é o dono da escrituração; a contraparte administrada usa coluna
    -- própria. Em XML já existem colunas separadas por lado.
    UPDATE efd_notas
       SET participante_id = NULL,
           contraparte_cliente_id = p_cliente_id
     WHERE participante_id = p_participante_id;

    UPDATE xml_notas
       SET emit_participante_id = NULL,
           emit_cliente_id = p_cliente_id
     WHERE emit_participante_id = p_participante_id;

    UPDATE xml_notas
       SET dest_participante_id = NULL,
           dest_cliente_id = p_cliente_id
     WHERE dest_participante_id = p_participante_id;

    -- Pivôs que só aceitam Participante deixam de ser a fonte do alvo. O histórico de
    -- consulta permanece em consulta_resultados.cliente_id.
    DELETE FROM consulta_lote_participantes
     WHERE participante_id = p_participante_id;

    DELETE FROM participantes_grupos_pivot
     WHERE participante_id = p_participante_id;

    UPDATE efd_importacoes AS importacao
       SET participante_ids = COALESCE((
           SELECT jsonb_agg(item)
             FROM jsonb_array_elements(importacao.participante_ids) AS item
            WHERE item <> to_jsonb(p_participante_id)
              AND item <> to_jsonb(p_participante_id::TEXT)
       ), '[]'::jsonb)
     WHERE jsonb_typeof(importacao.participante_ids) = 'array'
       AND EXISTS (
           SELECT 1
             FROM jsonb_array_elements(importacao.participante_ids) AS item
            WHERE item = to_jsonb(p_participante_id)
               OR item = to_jsonb(p_participante_id::TEXT)
       );

    UPDATE xml_importacoes AS importacao
       SET participante_ids = COALESCE((
           SELECT jsonb_agg(item)
             FROM jsonb_array_elements(importacao.participante_ids) AS item
            WHERE item <> to_jsonb(p_participante_id)
              AND item <> to_jsonb(p_participante_id::TEXT)
       ), '[]'::jsonb)
     WHERE jsonb_typeof(importacao.participante_ids) = 'array'
       AND EXISTS (
           SELECT 1
             FROM jsonb_array_elements(importacao.participante_ids) AS item
            WHERE item = to_jsonb(p_participante_id)
               OR item = to_jsonb(p_participante_id::TEXT)
       );

    -- O número da IM pertence ao CNPJ, não ao registro, e é resolvido UMA vez (caro/estável).
    -- Antes de descartar o participante, preserva a inscrição municipal já resolvida no cliente
    -- — só quando o cliente ainda não tem uma (a IM informada manualmente no cliente vence).
    UPDATE clientes AS c
       SET inscricao_municipal = p.inscricao_municipal
      FROM participantes AS p
     WHERE c.id = p_cliente_id
       AND p.id = p_participante_id
       AND COALESCE(p.inscricao_municipal, '') <> ''
       AND COALESCE(c.inscricao_municipal, '') = '';

    DELETE FROM participantes WHERE id = p_participante_id;
END
$$;

CREATE OR REPLACE FUNCTION fiscaldock_guardar_participante_exclusivo()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_documento TEXT;
BEGIN
    v_documento := fiscaldock_documento_normalizado(NEW.documento);
    PERFORM fiscaldock_travar_identidade(NEW.user_id, v_documento);

    -- O INSERT é SEMPRE aceito; a serialização acima é o único papel deste gatilho.
    --
    -- Antes cancelava a linha com RETURN NULL quando o documento já era cliente. Dois efeitos
    -- ruins: (1) a contraparte que também é cliente nunca ganhava linha de participante, então
    -- a nota nascia com participante_id NULL e o movimento ficava invisível às telas de
    -- participante (que filtram por participante_id); (2) cancelamento silencioso — o Eloquent
    -- não vê exceção e `create()` estoura num "Undefined array key 0" vindo do RETURNING vazio.
    --
    -- A exclusividade agora vale só para DUPLICATA DE CADASTRO (participante sem nenhuma nota),
    -- e é aplicada na consolidação, que sabe distinguir cadastro de movimento.
    RETURN NEW;
END
$$;

CREATE OR REPLACE FUNCTION fiscaldock_travar_cliente_exclusivo()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    PERFORM fiscaldock_travar_identidade(NEW.user_id, NEW.documento);
    RETURN NEW;
END
$$;

CREATE OR REPLACE FUNCTION fiscaldock_consolidar_cliente_exclusivo()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_documento TEXT;
    v_participante_id BIGINT;
BEGIN
    v_documento := fiscaldock_documento_normalizado(NEW.documento);

    IF v_documento = '' THEN
        RETURN NEW;
    END IF;

    FOR v_participante_id IN
        SELECT id
          FROM participantes
         WHERE user_id = NEW.user_id
           AND fiscaldock_documento_normalizado(documento) = v_documento
         ORDER BY id
    LOOP
        PERFORM fiscaldock_consolidar_participante_cliente(v_participante_id, NEW.id);
    END LOOP;

    RETURN NEW;
END
$$;

DROP TRIGGER IF EXISTS participantes_identidade_exclusiva_guard ON participantes;
CREATE TRIGGER participantes_identidade_exclusiva_guard
BEFORE INSERT OR UPDATE OF user_id, documento
ON participantes
FOR EACH ROW
EXECUTE FUNCTION fiscaldock_guardar_participante_exclusivo();

DROP TRIGGER IF EXISTS clientes_identidade_exclusiva_lock ON clientes;
CREATE TRIGGER clientes_identidade_exclusiva_lock
BEFORE INSERT OR UPDATE OF user_id, documento
ON clientes
FOR EACH ROW
EXECUTE FUNCTION fiscaldock_travar_cliente_exclusivo();

DROP TRIGGER IF EXISTS clientes_identidade_exclusiva_merge ON clientes;
CREATE TRIGGER clientes_identidade_exclusiva_merge
AFTER INSERT OR UPDATE OF user_id, documento
ON clientes
FOR EACH ROW
EXECUTE FUNCTION fiscaldock_consolidar_cliente_exclusivo();

-- Backfill idempotente das colisões existentes.
DO $$
DECLARE
    colisao RECORD;
BEGIN
    FOR colisao IN
        SELECT p.id AS participante_id, c.id AS cliente_id
          FROM participantes AS p
          JOIN clientes AS c
            ON c.user_id = p.user_id
           AND fiscaldock_documento_normalizado(c.documento)
               = fiscaldock_documento_normalizado(p.documento)
         WHERE fiscaldock_documento_normalizado(p.documento) <> ''
         ORDER BY p.user_id, p.id
    LOOP
        PERFORM fiscaldock_consolidar_participante_cliente(
            colisao.participante_id,
            colisao.cliente_id
        );
    END LOOP;
END
$$;

-- ---------------------------------------------------------------------------------------------
-- REPARO: notas cuja contraparte perdeu o participante_id numa consolidação anterior.
--
-- A primeira versão desta invariável apagava TODO participante que duplicasse um cliente,
-- inclusive os que tinham movimento, movendo o vínculo da nota para contraparte_cliente_id.
-- Como nenhuma superfície analítica lê essa coluna, o movimento sumia das telas de participante.
-- Este bloco recria a contraparte a partir do cadastro do cliente e devolve o participante_id,
-- preservando contraparte_cliente_id como marca aditiva. Idempotente.
-- ---------------------------------------------------------------------------------------------
INSERT INTO participantes (user_id, documento, razao_social, uf, municipio, created_at, updated_at)
SELECT DISTINCT
       c.user_id,
       fiscaldock_documento_normalizado(c.documento),
       c.razao_social,
       c.uf,
       c.municipio,
       NOW(),
       NOW()
  FROM efd_notas n
  JOIN clientes c ON c.id = n.contraparte_cliente_id
 WHERE n.participante_id IS NULL
   AND fiscaldock_documento_normalizado(c.documento) <> ''
   AND NOT EXISTS (
       SELECT 1
         FROM participantes p
        WHERE p.user_id = c.user_id
          AND fiscaldock_documento_normalizado(p.documento)
              = fiscaldock_documento_normalizado(c.documento)
   );

UPDATE efd_notas AS n
   SET participante_id = p.id
  FROM clientes AS c
  JOIN participantes AS p
    ON p.user_id = c.user_id
   AND fiscaldock_documento_normalizado(p.documento)
       = fiscaldock_documento_normalizado(c.documento)
 WHERE n.contraparte_cliente_id = c.id
   AND n.participante_id IS NULL;
