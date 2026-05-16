@push('structured-data')
    @include('landing_page.partials.breadcrumb-schema', [
        'trail' => [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Termos de Uso', 'url' => url('/termos')],
        ],
    ])
@endpush

<section class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">FiscalDock</p>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide mt-1">Termos de Uso</h1>
                <p class="text-xs text-gray-500 mt-1">Condições gerais para uso das páginas públicas, dos canais de contato e da plataforma FiscalDock.</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] uppercase tracking-wide text-gray-500">
                    <a href="{{ route('inicio') }}" class="hover:underline" style="color: #1e4fa0">Início</a>
                    <span>/</span>
                    <span>Termos de Uso</span>
                </div>
            </div>

            <div class="p-4 sm:p-6 space-y-6 text-sm text-gray-700 leading-relaxed">
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">1. Identificação</p>
                    <p>Estes Termos regulam a relação entre você ("Usuário") e a FiscalDock, operada por:</p>
                    <div class="mt-2 border border-gray-200 rounded px-3 py-2 bg-gray-50 text-[13px] text-gray-700 space-y-1">
                        <p><strong>F. DEVECCHI DAIGE E CIA LTDA</strong> — CNPJ 63.112.970/0001-07</p>
                        <p>Av. Marcelino Pires, 6385, Sala 7, Vila São Francisco, Dourados/MS, CEP 79.833-001</p>
                        <p>Contato: <a href="mailto:contato@fiscaldock.com.br" class="hover:underline" style="color: #1e4fa0">contato@fiscaldock.com.br</a> · (67) 99984-4366</p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">2. Definições</p>
                    <ul class="space-y-1 list-disc list-inside">
                        <li><strong>Plataforma</strong>: o sistema FiscalDock e suas páginas em <code>fiscaldock.com</code>.</li>
                        <li><strong>Usuário</strong>: pessoa física ou jurídica cadastrada para uso da Plataforma.</li>
                        <li><strong>Conta</strong>: credencial individual de acesso.</li>
                        <li><strong>Cliente</strong>: empresa cadastrada pelo Usuário dentro da Plataforma.</li>
                        <li><strong>Participante</strong>: terceiro (CNPJ) que aparece em documentos fiscais processados pelo Usuário.</li>
                        <li><strong>Créditos</strong>: unidades pré-pagas que custeiam consultas e funcionalidades, conforme tabela vigente.</li>
                        <li><strong>InfoSimples</strong>: integradora oficial utilizada para consultas a fontes públicas.</li>
                    </ul>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">3. Objeto e natureza do serviço</p>
                    <p>A FiscalDock é uma plataforma SaaS de monitoramento fiscal e tributário voltada a escritórios contábeis e empresas. Os resultados, relatórios e alertas têm caráter informativo e <strong>não substituem</strong> análise contábil, fiscal ou jurídica realizada por profissional habilitado. A contratação dos serviços não cria vínculo trabalhista, societário ou de prepostura.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">4. Cadastro, conta e elegibilidade</p>
                    <p>Ao se cadastrar, o Usuário declara ter pelo menos 18 anos e plena capacidade civil, fornecer dados verdadeiros, atualizados e completos, e manter o sigilo de suas credenciais. O Usuário é integralmente responsável por toda atividade realizada com a sua Conta. Suspeita de uso indevido deve ser comunicada imediatamente à FiscalDock.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">5. Planos, créditos e pagamentos</p>
                    <p>Os planos comerciais e suas faixas vigentes estão publicados em <a href="{{ route('precos') }}" class="hover:underline" style="color: #1e4fa0">/precos</a>. O Usuário concorda em pagar os valores referentes às faixas e aos créditos contratados. Cada consulta ou funcionalidade paga consome a quantidade de créditos divulgada na própria interface antes da execução. Faixas avançadas podem permanecer bloqueadas até a confirmação do primeiro pagamento, conforme regras comerciais da FiscalDock.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">6. Trial e bônus</p>
                    <p>Novos cadastros podem receber créditos de cortesia (atualmente 60 créditos válidos por 60 dias, sujeitos a alteração). Trials e bônus não são cumulativos com outras promoções, não são conversíveis em dinheiro e expiram automaticamente ao fim do prazo divulgado, ainda que não tenham sido utilizados.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">7. Uso permitido e vedações</p>
                    <p>O Usuário compromete-se a não:</p>
                    <ul class="mt-2 space-y-1 list-disc list-inside">
                        <li>Utilizar a Plataforma para fraude, lavagem de dinheiro ou qualquer fim ilícito;</li>
                        <li>Realizar scraping abusivo, engenharia reversa, decompilação ou tentativas de extrair código-fonte;</li>
                        <li>Compartilhar credenciais com terceiros não autorizados;</li>
                        <li>Inserir dados sabidamente falsos ou de terceiros sem base legal própria;</li>
                        <li>Enviar conteúdo malicioso ou comprometer a segurança, disponibilidade ou integridade dos serviços.</li>
                    </ul>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">8. Integrações externas</p>
                    <p>A FiscalDock utiliza integrações com n8n (orquestração interna), InfoSimples (consultas a fontes oficiais) e Mercado Pago (pagamentos), entre outros parceiros operacionais. Consultas executadas em provedores externos são pagas e o seu custo é repassado ao Usuário em créditos, conforme tabela vigente.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">9. Disponibilidade, manutenção e SLA</p>
                    <p>A FiscalDock envida esforços razoáveis para manter a Plataforma disponível, mas não garante operação ininterrupta. Janelas de manutenção, falhas de terceiros (InfoSimples, SEFAZ, provedores de e-mail, etc.) e eventos de caso fortuito ou força maior podem afetar temporariamente o serviço.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">10. Propriedade intelectual</p>
                    <p>A marca, o software, os layouts, os conteúdos e demais elementos da FiscalDock são protegidos por legislação aplicável. O Usuário recebe licença de uso pessoal, limitada e revogável durante a vigência destes Termos, vedada a reprodução comercial sem autorização escrita prévia.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">11. Limitação de responsabilidade</p>
                    <p>A FiscalDock não garante aprovação fiscal, regulatória, em licitação ou em qualquer outro processo decorrente do uso dos resultados gerados pela Plataforma. Cabe ao Usuário (e a seus profissionais habilitados) conferir e validar as informações antes de utilizá-las externamente. Salvo dolo ou culpa grave, a responsabilidade total da FiscalDock fica limitada ao valor efetivamente pago pelo Usuário nos 12 (doze) meses anteriores ao evento que originou a controvérsia.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">12. Suspensão e encerramento</p>
                    <p>A FiscalDock poderá suspender ou encerrar o acesso em caso de inadimplência, abuso, fraude, descumprimento destes Termos ou ordem judicial. Em caso de encerramento, o Usuário terá prazo razoável (mínimo de 30 dias) para exportar dados de sua titularidade. Dados fiscais permanecem retidos pelos prazos legais aplicáveis, mesmo após o encerramento.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">13. Alterações destes Termos</p>
                    <p>Estes Termos podem ser atualizados. Mudanças materiais serão comunicadas com antecedência razoável (em regra, 30 dias) por e-mail ao Usuário cadastrado e por aviso na Plataforma. O uso continuado após a vigência da nova versão implica aceite das alterações.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">14. Lei aplicável e foro</p>
                    <p>Estes Termos são regidos pela legislação brasileira. Fica eleito o foro da Comarca de Dourados/MS para dirimir qualquer controvérsia, com renúncia a qualquer outro, por mais privilegiado que seja, ressalvadas as hipóteses legais que assegurem foro diverso ao consumidor pessoa física.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">15. Disposições finais</p>
                    <p>Toda comunicação oficial será feita pelos canais cadastrados. Estes Termos representam o acordo integral entre as partes em relação ao seu objeto. A nulidade ou ineficácia de qualquer cláusula não compromete as demais. A FiscalDock pode ceder direitos e obrigações destes Termos no contexto de operações societárias, mediante comunicação prévia.</p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-[11px] text-gray-500">Última atualização: {{ now()->format('d/m/Y') }}.</p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Continuar navegação</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('privacidade') }}" class="bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium px-4 py-3 text-center">
                            Ler Política de Privacidade
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
