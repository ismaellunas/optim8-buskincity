<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Qirolab\Theme\Theme;

class RenderThemeSass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpack:theme-sass '.
        '{theme? : theme name} '.
        '{--change_dir= : change directory before execute the command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Render theme sass file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $theme = $this->argument('theme') ?? Theme::active();
        $changeDir = $this->option('change_dir');

        exec(
            (
                ($changeDir ? 'cd '.$changeDir.' && ' : '').
                'npx webpack --config webpack.config.biz.js --env theme='.$theme
            ),
            $outputs,
            $retval
        );

        foreach ($outputs as $output) {
            if ($retval === 0) {
                $this->info($output);
            } else {
                $this->error($output);
            }
        }

        return 0;
    }
}
