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
class UpdateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'ssl:update
                            {domains?* : 域名}';
    //{domain? : 需要更新 SSL 证书的域名，不填则是更新所有快过期的域名证书}
    //{--force : 携带此参数会忽略过期时间强制更新域名 SSL 证书}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新指定域名的 SSL 证书';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domains = $this->argument('domains');

        $ssl = new SSL(env('AK'), env('SK'));

        try {
            if ($domains) {
                $ssl->update($domains);
            } else {
                // $domains = $ssl->list(null, true);
                $domains = $ssl->list();

                // 获取那些 CertStatus 显示为 expire_soon 的域名
                $expired_soon_domains = [];
                foreach ($domains as $_domain) {
                    if ($_domain['CertStatus'] == 'expire_soon') {
                        array_push($expired_soon_domains, $_domain);
                    }
                }

                Log::info('--------[start]'.date('Y-m-d H:i:s').'--------');
                Log::info('有 '.count($expired_soon_domains).' 个域名 SSL 证书将过期');
                foreach ($expired_soon_domains as $_expired_soon_domain) {
                    // 测试
                    // $_expired_soon_domain['CertExpireTime'] = '2022-11-30T08:43:42Z';
                    $expired = strtotime($_expired_soon_domain['CertExpireTime']);
                    if (strtotime('+1 week') > $expired) { // 距离过期时间还有一周的时候更新
                        $this->update([$_expired_soon_domain['DomainName']]);
                        Log::info($expired_soon_domains['DomainName'].'证书已更新');
                    }
                }
                Log::info('--------[end]'.date('Y-m-d H:i:s').'--------');
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
