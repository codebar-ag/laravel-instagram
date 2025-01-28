<?php

namespace CodebarAg\LaravelInstagram\Commands;

use Illuminate\Console\Command;

class LaravelInstagramCommand extends Command
{
    public $signature = 'laravel-instagram';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
