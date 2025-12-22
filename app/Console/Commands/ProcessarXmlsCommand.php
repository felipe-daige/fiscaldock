<?php

namespace App\Console\Commands;

use App\Models\XmlDocumento;
use App\Services\XmlClassificationService;
use Illuminate\Console\Command;

class ProcessarXmlsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml:processar {--ids= : IDs específicos dos documentos (separados por vírgula)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa documentos XML pendentes e gera sugestões de lançamento';

    /**
     * Execute the console command.
     */
    public function handle(XmlClassificationService $classificationService)
    {
        $this->info('Processando documentos XML...');

        $query = XmlDocumento::where('status', 'pendente');

        if ($this->option('ids')) {
            $ids = explode(',', $this->option('ids'));
            $query->whereIn('id', $ids);
        }

        $documentos = $query->get();

        if ($documentos->isEmpty()) {
            $this->warn('Nenhum documento pendente encontrado.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$documentos->count()} documento(s) pendente(s).");

        $bar = $this->output->createProgressBar($documentos->count());
        $bar->start();

        $processados = 0;
        $erros = 0;

        foreach ($documentos as $documento) {
            try {
                $sugestao = $classificationService->classificar($documento);
                $lancamento = $classificationService->criarLancamentoSugerido($documento, $sugestao);
                $documento->update(['status' => 'processado']);
                $processados++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Erro ao processar documento ID {$documento->id}: {$e->getMessage()}");
                $erros++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Processamento concluído!");
        $this->info("Processados: {$processados}");
        if ($erros > 0) {
            $this->warn("Erros: {$erros}");
        }

        return Command::SUCCESS;
    }
}




