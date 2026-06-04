<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OrgSyncService;

class SyncOrgData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:org-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data user dan struktur organisasi dari API eksternal';

    /**
     * Execute the console command.
     */
    public function handle(OrgSyncService $syncService): int
    {
        $this->info('Memulai proses sinkronisasi dari API eksternal...');
        
        try {
            $stats = $syncService->sync();
            
            $this->newLine();
            $this->info('Sinkronisasi selesai dengan sukses!');
            
            $headers = ['Entitas', 'Jumlah Terproses'];
            $data = [
                ['Director', $stats['directors']],
                ['Divisi', $stats['divisis']],
                ['Department', $stats['departments']],
                ['Section', $stats['sections']],
                ['Unit', $stats['units']],
                ['Role', $stats['roles']],
                ['Position', $stats['positions']],
                ['Bagian Kerja', $stats['bagian_kerja']],
                ['User', $stats['users']],
                ['Gagal (Error)', $stats['errors']],
            ];
            
            $this->table($headers, $data);
            
            if ($stats['errors'] > 0) {
                $this->warn("Perhatian: Ada {$stats['errors']} data yang gagal disinkronkan. Cek log laravel untuk detailnya.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Terjadi error saat sinkronisasi: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
