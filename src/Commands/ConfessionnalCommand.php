<?php

namespace BlackpigCreatif\Confessionnal\Commands;

use Illuminate\Console\Command;

class ConfessionnalCommand extends Command
{
    public $signature = 'confessionnal';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
