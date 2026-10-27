<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-logs:prune
                            {--days=90 : Quantidade de dias a reter (padrão: 90 dias)}
                            {--category=navigation : Categoria de logs a expurgar (padrão: navigation)}
                            {--dry-run : Simula o expurgo sem excluir nenhum registro}
                            {--force : Executa o expurgo real sem pedir confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expurgo controlado e manutenção de logs de atividades antigos (com suporte a dry-run)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $category = $this->option('category');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($days <= 0) {
            $this->error('A quantidade de dias deve ser maior que zero.');
            return self::FAILURE;
        }

        $cutoffDate = now()->subDays($days);

        $query = ActivityLog::where('created_at', '<', $cutoffDate);

        if (!empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        $count = $query->count();

        $this->info("=== Manutenção e Retenção de Logs de Atividades ===");
        $this->line("Data limite de corte: " . $cutoffDate->format('d/m/Y H:i:s') . " (anterior a {$days} dias)");
        $this->line("Filtro de categoria: " . ($category ?: 'Todas'));
        $this->line("Registros elegíveis para expurgo: <fg=yellow>{$count}</>");

        if ($count === 0) {
            $this->info("Nenhum log antigo elegível encontrado. Operação concluída.");
            return self::SUCCESS;
        }

        // Modo Simulação (Dry-Run)
        if ($dryRun) {
            $this->warn("[MODO SIMULAÇÃO ATIVO] Nenhum registro foi excluído do banco de dados.");
            $this->line("Para executar a exclusão definitiva, execute novamente o comando sem a flag --dry-run e com --force.");
            return self::SUCCESS;
        }

        // Confirmação de segurança se não foi passada a flag --force
        if (!$force && !$this->confirm("Deseja realmente excluir permanentemente esses {$count} registros de log?")) {
            $this->warn("Operação cancelada pelo usuário. Nenhum dado foi afetado.");
            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Sucesso! {$deleted} registros de log foram excluídos com sucesso.");

        return self::SUCCESS;
    }
}
