<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LimparXmlTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml:limpar-temp {--hours=24 : Remover pastas mais antigas que X horas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove pastas temporárias de importação de XML mais antigas que o limite especificado';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pasta = storage_path('app/temp/xml-imports');
        $horas = (int) $this->option('hours');
        $limite = now()->subHours($horas);

        if (!is_dir($pasta)) {
            $this->info('Pasta de importações temporárias não existe.');
            return self::SUCCESS;
        }

        $removidos = 0;
        $erros = 0;

        foreach (glob("{$pasta}/*", GLOB_ONLYDIR) as $dir) {
            $modificadoEm = filemtime($dir);

            if ($modificadoEm < $limite->timestamp) {
                try {
                    File::deleteDirectory($dir);
                    $this->line("Removido: {$dir}");
                    $removidos++;
                } catch (\Exception $e) {
                    $this->error("Erro ao remover {$dir}: {$e->getMessage()}");
                    Log::error('LimparXmlTemp: erro ao remover pasta', [
                        'pasta' => $dir,
                        'erro' => $e->getMessage(),
                    ]);
                    $erros++;
                }
            }
        }

        $this->info("Limpeza concluída: {$removidos} pasta(s) removida(s), {$erros} erro(s).");

        if ($removidos > 0) {
            Log::info('LimparXmlTemp: limpeza executada', [
                'removidos' => $removidos,
                'erros' => $erros,
                'limite_horas' => $horas,
            ]);
        }

        return $erros > 0 ? self::FAILURE : self::SUCCESS;
    }
}
