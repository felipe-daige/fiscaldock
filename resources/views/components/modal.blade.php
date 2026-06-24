@props(['id', 'titulo' => ''])
{{--
    Modal padrão do design system. Cache-robusto: abre/fecha por onclick inline
    togglando `hidden` — sem dependência de JS-file (imune ao cache de 1h do bi.js
    e afins). Abrir: onclick="document.getElementById('ID').classList.remove('hidden')".
    Fechar: × no header, clique no overlay, ou tecla Esc.
--}}
<div id="{{ $id }}"
     class="hidden fixed inset-0 z-50 flex items-center justify-center px-4"
     style="background-color: rgba(0,0,0,0.5);"
     onclick="if(event.target===this)this.classList.add('hidden')"
     onkeydown="if(event.key==='Escape')this.classList.add('hidden')"
     tabindex="-1">
    <div class="bg-white rounded border border-gray-300 w-full max-w-md"
         style="box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-[11px] font-semibold text-gray-600 uppercase tracking-widest">{{ $titulo }}</h3>
            <button type="button" aria-label="Fechar"
                    class="text-gray-400 hover:text-gray-700 text-xl leading-none"
                    onclick="document.getElementById('{{ $id }}').classList.add('hidden')">&times;</button>
        </div>
        <div class="p-4">
            {{ $slot }}
        </div>
    </div>
</div>
