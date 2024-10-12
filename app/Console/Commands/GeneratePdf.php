<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePdf extends Command
{
    protected $signature = 'pdf:generate';
    protected $description = 'Generate PDF using Puppeteer';

    public function handle()
    {
        $command = 'node ' . base_path('scripts/generate-pdf.js');
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            $this->info('PDF generated successfully.');
        } else {
            $this->error('Failed to generate PDF.');
        }
    }
}
