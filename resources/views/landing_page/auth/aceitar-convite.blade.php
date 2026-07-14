<section class="min-h-[75vh] flex items-center justify-center px-4 py-16" style="background-color:#f3f4f6">
    <div class="w-full max-w-lg bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-5 text-white" style="background-color:#0b1f3a">
            <p class="text-xs font-bold uppercase tracking-wider opacity-70">Convite de equipe</p>
            <h1 class="text-xl font-bold mt-1">Entrar em {{ $invitation->account->nome }}</h1>
            <p class="text-sm opacity-80 mt-2">Seu acesso será individual e usará o saldo compartilhado da conta.</p>
        </div>

        <div class="p-6">
            @if($errors->any())
                <div class="mb-4 border-l-4 p-3 text-sm" style="border-left-color:#dc2626;background-color:#fef2f2;color:#991b1b">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            @if($loggedUser && !$emailMatches)
                <p class="text-sm text-gray-700">Este convite foi enviado para <strong>{{ $invitation->email }}</strong>, mas você está conectado como <strong>{{ $loggedUser->email }}</strong>.</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-5">@csrf<button class="w-full rounded px-4 py-3 text-sm font-bold text-white" style="background-color:#0b1f3a">Sair para trocar de conta</button></form>
            @else
                <form method="POST" action="{{ route('equipe.convite.confirmar', ['token' => $token]) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">E-mail convidado</label>
                        <input value="{{ $invitation->email }}" disabled class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm bg-gray-50">
                    </div>

                    @if(!$loggedUser)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Nome</label><input name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Sobrenome</label><input name="sobrenome" value="{{ old('sobrenome') }}" required class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm"></div>
                        </div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Telefone</label><input name="telefone" value="{{ old('telefone') }}" required class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm"></div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Senha</label><input type="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Confirmar senha</label><input type="password" name="password_confirmation" required class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm"></div>
                        </div>
                        <label class="flex items-start gap-2 text-xs text-gray-600"><input type="checkbox" name="terms_aceitos" value="1" required class="mt-0.5"><span>Li e aceito os <a href="/termos" target="_blank" class="underline">Termos de Uso</a> e a <a href="/privacidade" target="_blank" class="underline">Política de Privacidade</a>.</span></label>
                    @endif

                    <button class="w-full rounded px-4 py-3 text-sm font-bold text-white" style="background-color:#0b1f3a">Aceitar convite</button>
                </form>
            @endif
        </div>
    </div>
</section>
