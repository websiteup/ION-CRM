<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Proposal;
use App\Models\ProposalHistory;
use Carbon\Carbon;

class ExpireProposals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proposals:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marchează ofertele expirate ca fiind expirate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        $expiredProposals = Proposal::where('valid_until', '<', $today)
            ->where('status', '!=', 'expired')
            ->where('status', '!=', 'accepted')
            ->where('status', '!=', 'rejected')
            ->get();

        $count = 0;
        foreach ($expiredProposals as $proposal) {
            $proposal->update([
                'status' => 'expired'
            ]);
            
            // Add history event
            ProposalHistory::create([
                'proposal_id' => $proposal->id,
                'event_type' => 'expired',
                'title' => 'Ofertă expirată',
                'description' => 'Oferta a expirat automat',
                'changes' => null,
                'user_id' => null, // System action
                'event_date' => now(),
            ]);
            
            $count++;
        }

        $this->info("Au fost marcate {$count} oferte ca expirate.");
        
        return Command::SUCCESS;
    }
}

