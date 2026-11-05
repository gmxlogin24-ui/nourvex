<?php

namespace App\Console\Commands;

use App\Models\InvestmentProject;
use App\Services\InvestmentService;
use App\Services\InvestorGate;
use Illuminate\Console\Command;

class ExpireInvestments extends Command
{
    protected $signature = 'investments:expire';
    protected $description = 'Refund every investor in a project that passed its deadline without reaching the goal.';

    public function handle(InvestmentService $svc, InvestorGate $gate): int
    {
        $ids = InvestmentProject::where('status', 'open')->where('closes_at', '<', now())->pluck('id');

        foreach ($ids as $id) {
            $project = InvestmentProject::find($id);
            if (! $project) {
                continue;
            }
            try {
                $count = $svc->cancel($project);
                $gate->audit(null, 'auto_refund', 'project', (int) $project->id, $count . ' investment(s) refunded, deadline passed without reaching the goal');
                $this->line("project {$project->id}: {$count} investment(s) refunded");
            } catch (\Throwable $e) {
                report($e);
                $this->error("project {$project->id}: " . $e->getMessage());
            }
        }

        $this->info(count($ids) . ' expired project(s) processed.');

        return self::SUCCESS;
    }
}
