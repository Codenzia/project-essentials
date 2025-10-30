<?php

namespace Codenzia\ProjectEssentials\Commands;

use Illuminate\Console\Command;

class ProjectEssentialCommand extends Command
{
    public $signature = 'project-essential';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
