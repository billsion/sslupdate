<?php

namespace App\Console\Commands\SSL;

use App\Services\SSL;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class ListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'ssl:list
                            {keywords? : 域名关键字}
                            {--F|force : 此参数用来确定是否要更新域名缓存}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取所有证书信息';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ssl      = new SSL(env('AK'), env('SK'));
        $keywords = $this->argument('keywords');
        $force    = $this->option('force');

        try {
            $domain_records = $ssl->list($keywords, $force ?? false);
            if ($force) {
                $this->warn('远程拉取');
            }
            $this->table(array_keys($domain_records[0]), $domain_records);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
