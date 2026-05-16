@push('structured-data')
    @include('landing_page.partials.breadcrumb-schema', [
        'trail' => [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Política de Privacidade', 'url' => url('/privacidade')],
        ],
    ])
@endpush

<section class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">FiscalDock</p>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide mt-1">Política de Privacidade</h1>
                <p class="text-xs text-gray-500 mt-1">Como a FiscalDock trata dados pessoais nos seus canais públicos, processos comerciais e na plataforma.</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] uppercase tracking-wide text-gray-500">
                    <a href="{{ route('inicio') }}" class="hover:underline" style="color: #1e4fa0">Início</a>
                    <span>/</span>
                    <span>Política de Privacidade</span>
                </div>
            </div>

            <div class="p-4 sm:p-6 space-y-6 text-sm text-gray-700 leading-relaxed">

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">1. Identificação do controlador</p>
                    <div class="border border-gray-200 rounded px-3 py-2 bg-gray-50 text-[13px] text-gray-700 space-y-1">
                        <p><strong>F. DEVECCHI DAIGE E CIA LTDA</strong> — CNPJ 63.112.970/0001-07</p>
                        <p>Av. Marcelino Pires, 6385, Sala 7, Vila São Francisco, Dourados/MS, CEP 79.833-001</p>
                        <p>Contato: <a href="mailto:contato@fiscaldock.com.br" class="hover:underline" style="color: #1e4fa0">contato@fiscaldock.com.br</a> · (67) 99984-4366</p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">2. Encarregado de Tratamento de Dados (DPO)</p>
                    <p>Para assuntos de privacidade, contato direto pelo e-mail <a href="mailto:contato@fiscaldock.com.br" class="hover:underline" style="color: #1e4fa0">contato@fiscaldock.com.br</a>, identificando o assunto como "LGPD — Encarregado".</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">3. Definições</p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li><strong>Titular</strong>: pessoa natural a quem se referem os dados.</li>
                        <li><strong>Dado pessoal</strong>: informação relacionada a pessoa natural identificada ou identificável.</li>
                        <li><strong>Tratamento</strong>: toda operação realizada com dados pessoais (coleta, armazenamento, uso, compartilhamento, eliminação, etc.).</li>
                        <li><strong>Controlador</strong>: quem decide sobre o tratamento.</li>
                        <li><strong>Operador</strong>: quem trata dados em nome do controlador.</li>
                        <li><strong>Sub-operador</strong>: parceiro técnico contratado pelo operador para parte do tratamento.</li>
                    </ul>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">4. Papel da FiscalDock no tratamento</p>
                    <p>A FiscalDock pode atuar em duas qualidades:</p>
                    <ul class="mt-2 space-y-1 list-disc list-inside">
                        <li><strong>Controladora</strong> dos dados pessoais do Usuário cadastrado (nome, sobrenome, e-mail, telefone, empresa, cargo, CPF/CNPJ, dados de uso da Plataforma).</li>
                        <li><strong>Operadora</strong> dos dados de terceiros que o Usuário insere ou consulta — por exemplo, CNPJs de Participantes, dados de notas fiscais (XML/SPED) e retornos de consultas InfoSimples/SEFAZ. Nessa qualidade, o Usuário é o controlador desses dados perante a LGPD e cabe a ele dispor de base legal própria para inseri-los na Plataforma.</li>
                    </ul>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">5. Dados pessoais coletados</p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li><strong>Cadastrais</strong>: nome, sobrenome, e-mail, telefone, empresa, cargo, CPF ou CNPJ, faixa de faturamento, principal desafio operacional.</li>
                        <li><strong>De uso e navegação</strong>: endereço IP, agente de navegação (user-agent), páginas acessadas, ações realizadas, logs de autenticação.</li>
                        <li><strong>Financeiros</strong>: dados de transação repassados pelo gateway de pagamento (a FiscalDock não armazena número de cartão completo).</li>
                        <li><strong>Inseridos pelo Usuário</strong>: dados pessoais eventualmente contidos em documentos fiscais e consultas operadas em nome do Usuário.</li>
                    </ul>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">6. Finalidades e bases legais</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px] border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="bg-gray-50 px-3 py-2 text-left font-semibold text-gray-700 border-b border-gray-200">Finalidade</th>
                                    <th class="bg-gray-50 px-3 py-2 text-left font-semibold text-gray-700 border-b border-gray-200">Base legal (Art. 7º LGPD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td class="px-3 py-2 border-b border-gray-100 align-top">Prestação do serviço contratado</td><td class="px-3 py-2 border-b border-gray-100 align-top">Execução de contrato (V)</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100 align-top">Cobrança, faturamento e obrigações fiscais</td><td class="px-3 py-2 border-b border-gray-100 align-top">Cumprimento de obrigação legal ou regulatória (II)</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100 align-top">Suporte e atendimento</td><td class="px-3 py-2 border-b border-gray-100 align-top">Execução de contrato (V)</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100 align-top">Prevenção a fraude e segurança</td><td class="px-3 py-2 border-b border-gray-100 align-top">Legítimo interesse (IX)</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100 align-top">Melhoria do produto (análise agregada)</td><td class="px-3 py-2 border-b border-gray-100 align-top">Legítimo interesse (IX)</td></tr>
                                <tr><td class="px-3 py-2 align-top">Envio de novidades e comunicações de marketing</td><td class="px-3 py-2 align-top">Consentimento (I), revogável a qualquer momento</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">7. Compartilhamento e sub-operadores</p>
                    <p>A FiscalDock não comercializa dados pessoais. Compartilhamentos ocorrem apenas com sub-operadores necessários à prestação do serviço, abaixo identificados:</p>
                    <ul class="mt-2 space-y-1 list-disc list-inside">
                        <li><strong>n8n</strong> — orquestração de webhooks, hospedado pela própria FiscalDock no Brasil.</li>
                        <li><strong>InfoSimples</strong> (Brasil) — enriquecimento cadastral e consultas a fontes oficiais (CND, SEFAZ, NF-e/CT-e), sempre sob comando do Usuário.</li>
                        <li><strong>Provedor de infraestrutura/hospedagem</strong> — [a confirmar antes da publicação definitiva].</li>
                        <li><strong>Mercado Pago</strong> (Brasil) — processamento de pagamentos.</li>
                        <li><strong>Provedor de e-mail transacional</strong> — [a confirmar antes da publicação definitiva].</li>
                    </ul>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">8. Transferência internacional de dados</p>
                    <p>Atualmente, os dados pessoais tratados pela FiscalDock permanecem em território nacional. Caso, no futuro, qualquer sub-operador opere fora do Brasil, será adotada salvaguarda compatível com os Arts. 33 a 36 da LGPD, e esta política será atualizada com a identificação do país e da garantia aplicada.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">9. Retenção</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px] border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="bg-gray-50 px-3 py-2 text-left font-semibold text-gray-700 border-b border-gray-200">Categoria</th>
                                    <th class="bg-gray-50 px-3 py-2 text-left font-semibold text-gray-700 border-b border-gray-200">Prazo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td class="px-3 py-2 border-b border-gray-100">Conta ativa</td><td class="px-3 py-2 border-b border-gray-100">Enquanto durar a relação</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100">Conta cancelada (dados cadastrais)</td><td class="px-3 py-2 border-b border-gray-100">5 anos após o encerramento</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100">Leads não convertidos</td><td class="px-3 py-2 border-b border-gray-100">12 meses a partir do contato</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100">Logs de acesso (IP, autenticação)</td><td class="px-3 py-2 border-b border-gray-100">6 meses (mínimo Marco Civil)</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100">Dados fiscais (XML, SPED, consultas)</td><td class="px-3 py-2 border-b border-gray-100">5 anos (decadência tributária)</td></tr>
                                <tr><td class="px-3 py-2 border-b border-gray-100">Dados financeiros e transações</td><td class="px-3 py-2 border-b border-gray-100">10 anos</td></tr>
                                <tr><td class="px-3 py-2">Consentimento de marketing</td><td class="px-3 py-2">Até revogação pelo titular</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">10. Direitos do titular (Art. 18 LGPD)</p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li>Confirmação da existência de tratamento;</li>
                        <li>Acesso aos dados;</li>
                        <li>Correção de dados incompletos, inexatos ou desatualizados;</li>
                        <li>Anonimização, bloqueio ou eliminação de dados desnecessários, excessivos ou tratados em desconformidade;</li>
                        <li>Portabilidade a outro fornecedor;</li>
                        <li>Eliminação dos dados tratados com base no consentimento;</li>
                        <li>Informação sobre o compartilhamento;</li>
                        <li>Informação sobre a possibilidade de não consentir e suas consequências;</li>
                        <li>Revogação do consentimento.</li>
                    </ul>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">11. Como exercer seus direitos</p>
                    <p>Solicitações relacionadas à privacidade podem ser feitas por <a href="mailto:contato@fiscaldock.com.br" class="hover:underline" style="color: #1e4fa0">contato@fiscaldock.com.br</a>. A FiscalDock responderá em até <strong>15 dias</strong> (Art. 19, LGPD), podendo exigir confirmação de identidade para proteger o próprio titular.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">12. Cookies</p>
                    <p>O uso de cookies está descrito na <a href="{{ route('cookies') }}" class="hover:underline" style="color: #1e4fa0">Política de Cookies</a>. As preferências podem ser revistas a qualquer momento pelo botão abaixo.</p>
                    <button type="button" data-action="open-cookie-settings"
                            class="mt-3 inline-flex items-center justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Configurar cookies
                    </button>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">13. Crianças e adolescentes</p>
                    <p>A FiscalDock é um serviço B2B voltado a maiores de 18 anos. Não há coleta intencional de dados pessoais de crianças e adolescentes. Caso identifiquemos coleta inadvertida, os dados serão eliminados imediatamente.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">14. Segurança da informação</p>
                    <p>Adotamos medidas técnicas e administrativas razoáveis para reduzir riscos de acesso não autorizado, perda, alteração ou divulgação indevida — incluindo transporte criptografado (TLS), armazenamento de senhas com algoritmos de hash robustos, isolamento de dados por Usuário, controle de acesso por papel, logs de auditoria e backups regulares. Nenhum ambiente, contudo, é totalmente imune a incidentes.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">15. Incidentes de segurança</p>
                    <p>Na hipótese de incidente que possa acarretar risco ou dano relevante aos titulares, a FiscalDock comunicará a Autoridade Nacional de Proteção de Dados (ANPD) e os titulares afetados em prazo razoável, nos termos do Art. 48 da LGPD.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">16. Atualizações desta política</p>
                    <p>Esta política pode ser atualizada para refletir mudanças na Plataforma ou na legislação. Alterações materiais serão comunicadas por banner na Plataforma e/ou e-mail. A versão atualmente vigente é a publicada nesta página.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">17. Autoridade Nacional de Proteção de Dados (ANPD)</p>
                    <p>O titular pode levar reclamações à ANPD pelo portal oficial <a href="https://www.gov.br/anpd" target="_blank" rel="noopener" class="hover:underline" style="color: #1e4fa0">gov.br/anpd</a>.</p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-[11px] text-gray-500">Última atualização: {{ now()->format('d/m/Y') }}.</p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Continuar navegação</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('termos') }}" class="bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium px-4 py-3 text-center">
                            Ler Termos de Uso
                        </a>
                        <a href="{{ route('inicio') }}" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium px-4 py-3 text-center">
                            Voltar ao início
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
