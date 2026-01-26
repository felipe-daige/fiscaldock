// ============================================================
// Code Node: Gerar Resumo da Importacao XML
// ============================================================
// Processa os dois formatos de input:
// - DADOS NOVOS: campos no root (emit_participante_id, valor_total, etc)
// - JA EXISTENTES: estrutura { metadata, emissor, destinatario }
//
// Gera campos formatados para SQL (NULL-safe) para evitar expressoes
// complexas no PostgreSQL node do n8n.
// ============================================================

const items = $input.all();

if (!Array.isArray(items) || items.length === 0) {
  return [{ json: { error: true, message: "Nenhum item recebido" } }];
}

/** =========================
 * Helpers
 * ========================= */
const toInt = (v, fallback = 0) => {
  const n = Number.parseInt(String(v ?? ""), 10);
  return Number.isFinite(n) ? n : fallback;
};

const toFloat = (v, fallback = 0) => {
  const n = Number.parseFloat(String(v ?? ""));
  return Number.isFinite(n) ? n : fallback;
};

const addId = (set, v) => {
  const n = toInt(v, NaN);
  if (Number.isFinite(n)) set.add(n);
};

const safeStr = (v, fallback = "") => (v == null ? fallback : String(v));

// Escapa aspas simples para SQL
const escapeSQL = (str) => str ? str.replace(/'/g, "''") : "";

/** =========================
 * Detectar formato
 * ========================= */
const firstJson = items[0].json ?? {};
const isExistente = Boolean(firstJson?.metadata?.tab_id);

/** =========================
 * Extrair metadados
 * ========================= */
let user_id = 0;
let tab_id = "";
let importacao_id = 0;
let progress_url = "";
let total_xmls = items.length;

if (isExistente) {
  const m = firstJson.metadata ?? {};
  user_id = toInt(m.user_id, 0);
  tab_id = safeStr(m.tab_id, "");
  importacao_id = toInt(m.importacao_id, 0);
  progress_url = safeStr(m.progress_url, "");
  total_xmls = toInt(m.total_xmls, items.length);
} else {
  user_id = toInt(firstJson.user_id, 0);
  tab_id = safeStr(firstJson?.payload?.metadata?.tab_id, "");
  importacao_id = toInt(firstJson.importacao_xml_id, 0);
  progress_url = safeStr(firstJson?.payload?.metadata?.progress_url, "");
  total_xmls = toInt(firstJson?.payload?.metadata?.total_xmls, items.length);
}

/** =========================
 * Coletar participantes, valores e erros
 * ========================= */
const participanteIds = new Set();
let valorTotal = 0;
const erros = [];
let xmls_com_erro = 0;

for (const it of items) {
  const d = it?.json ?? {};

  // Coletar erros
  if (d.erro || d.error || d.erro_codigo || d.error_code) {
    xmls_com_erro++;
    erros.push({
      arquivo: safeStr(d.arquivo || d.filename, 'desconhecido'),
      erro: safeStr(d.erro_msg || d.error_message || d.erro || d.error, 'Erro desconhecido'),
      codigo: safeStr(d.erro_codigo || d.error_code, 'UNKNOWN_ERROR'),
      chave: safeStr(d.chave_acesso, null)
    });
  }

  if (isExistente) {
    addId(participanteIds, d?.emissor?.id);
    addId(participanteIds, d?.destinatario?.id);
  } else {
    addId(participanteIds, d.emit_participante_id);
    addId(participanteIds, d.dest_participante_id);
    if (d.valor_total != null) {
      valorTotal += toFloat(d.valor_total, 0);
    }
  }
}

const participante_ids = Array.from(participanteIds).sort((a, b) => a - b);
const xmls_processados = items.length;

/** =========================
 * Campos de erro
 * ========================= */
const erro_mensagem = erros.length > 0
  ? `${erros.length} XML(s) com erro no processamento`
  : null;

const erros_detalhados = erros.length > 0
  ? { xmls_com_erro: erros, total_erros: erros.length }
  : null;

/** =========================
 * Campos formatados para SQL (com tratamento de NULL)
 * ========================= */
const participante_ids_sql = participante_ids.length > 0
  ? `'${JSON.stringify(participante_ids)}'::jsonb`
  : 'NULL';

const erro_mensagem_sql = erro_mensagem
  ? `'${escapeSQL(erro_mensagem)}'`
  : 'NULL';

const erros_detalhados_sql = erros_detalhados
  ? `'${escapeSQL(JSON.stringify(erros_detalhados))}'::jsonb`
  : 'NULL';

/** =========================
 * Output
 * ========================= */
const status = erros.length > 0 && (xmls_processados - xmls_com_erro) === 0
  ? 'erro'
  : 'concluido';

const output = {
  user_id,
  tab_id,
  importacao_id,
  progress_url,

  xmls_processados,
  xmls_novos: isExistente ? 0 : xmls_processados - xmls_com_erro,
  xmls_duplicados_processados: isExistente ? xmls_processados : 0,
  xmls_com_erro,
  total_xmls,

  participantes_novos: isExistente ? 0 : participante_ids.length,
  participantes_atualizados: isExistente ? participante_ids.length : 0,
  participantes_ignorados: 0,
  participante_ids,

  valor_total: valorTotal.toFixed(2),

  // Campos de erro (objetos originais)
  erro_mensagem,
  erros_detalhados,

  // Campos formatados para SQL (usar direto no PostgreSQL node)
  participante_ids_sql,
  erro_mensagem_sql,
  erros_detalhados_sql,

  status,
  concluido_em: new Date().toISOString(),
  progresso: 100,

  mensagem: status === 'erro'
    ? `Erro! ${xmls_com_erro} XMLs com falha.`
    : isExistente
      ? `Concluido! ${xmls_processados} XMLs (ja existentes), ${participante_ids.length} participantes.`
      : `Concluido! ${xmls_processados} XMLs novos, ${participante_ids.length} participantes.`,
};

return [{ json: output }];
