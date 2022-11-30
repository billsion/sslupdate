<?php

namespace App\Console\Commands\SSL;

use App\Services\SSL;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class deletePostsCommand.
 *
 * @category Console_Command
 */
class FindCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'ssl:find
                            {domain : 域名}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取指定域名的证书信息';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $ssl = new SSL(env('AK'), env('SK'));
            if ($this->hasArgument('domain')) {
                $result = $ssl->findByDomain($this->argument(('domain')));
            } else {
                throw new Exception('缺少参数');
            }
            print_r($result['CertInfos']['CertInfo'][0]);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
