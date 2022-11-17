<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;
use function Termwind\{render};

class ListWebsites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:list-websites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List websites to be health checked periodically';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $websiteHtml = Website::all()->map(function (Website $website) {
            $healthyText = $website->is_healthy ? 'Healthy' : 'Not healthy';
            $healthyClass = $website->is_healthy ? '' : 'text-red-500';

            return <<<HTML
                <div class="flex {$healthyClass}">
                    <span class="font-bold mr-1">[{$website->id}]</span>
                    <span>{$website->url}­</span>
                    <span class="flex-1 content-repeat-['.']">.</span>
                    <span class="uppercase">­{$healthyText}</span>
                </div>
            HTML;
        })->join('');

        render(<<<HTML
            <div class="mx-2 my-1">
                {$websiteHtml}
            </div>
        HTML
        );

        return Command::SUCCESS;
    }
}
