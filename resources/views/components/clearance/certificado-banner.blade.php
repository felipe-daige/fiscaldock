@if($exibir)
    <section
        data-clearance-certificado-banner
        aria-labelledby="clearance-certificado-banner-titulo"
        class="mb-4 sm:mb-6 rounded border p-4"
        style="background-color: #eff6ff; border-color: #bfdbfe"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <span
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded"
                    style="background-color: #dbeafe; color: #1d4ed8"
                    aria-hidden="true"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3.75h7.5L18.75 8.25v7.5a2.5 2.5 0 01-2.5 2.5H7.75a2.5 2.5 0 01-2.5-2.5v-9.5a2.5 2.5 0 012.5-2.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.25 3.75v4.5h4.5M8.25 7.5h3.25" />
                        <circle cx="11.25" cy="12" r="2.25" stroke-width="1.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 13.7l-.55 3.05 2.05-1.15 2.05 1.15-.55-3.05" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 id="clearance-certificado-banner-titulo" class="text-sm font-semibold" style="color: #1e3a8a">
                        Cadastre o certificado digital A1
                    </h2>
                    <p class="mt-1 text-xs leading-relaxed" style="color: #1e40af">
                        Sem o certificado, a consulta à SEFAZ é pública e pode mascarar a contraparte e omitir tributos e detalhes dos itens. Cadastre o A1 da empresa para obter o retorno completo.
                    </p>
                </div>
            </div>
            <a
                href="/app/minha-empresa#certificado-digital"
                data-link
                class="auth-control inline-flex shrink-0 items-center justify-center gap-2 self-start rounded text-white transition-colors hover:opacity-90 sm:self-center"
                style="background-color: #1d4ed8"
            >
                Cadastrar certificado
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </section>
@endif
