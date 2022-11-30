<?php

namespace App\Services;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Dcdn\Dcdn;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class SSL
{
    public function __construct(string $ak, string $sk)
    {
        AlibabaCloud::accessKeyClient($ak, $sk)
            ->regionId('cn-shanghai')
            ->asDefaultClient()->options([]);
    }

    /**
     * 判断文件是否存在.
     *
     * @param string $file_path 文件路径
     *
     * @throws FileNotFoundException
     *
     * @return string 文件内容
     */
    private function __getSSLFile(string $file_path): string
    {
        $file_info = pathinfo($file_path);
        if (!file_exists($file_path)) {
            throw new FileNotFoundException($file_info['dirname'].' 目录下未找到后缀为'.$file_info['extension'].'的文件');
        }

        return file_get_contents($file_path);
    }

    /**
     * 更新 SSL 证书到阿里云 DCDN 上.
     *
     * @param array $domains 域名数组
     */
    public function update(array $domains = null): array
    {
        $cert_base_path = env('SSL_PATH');

        foreach ($domains as $domain) {
            $saveCertName = $domain.date('Ymd');

            try {
                $sslPub = $this->__getSSLFile($cert_base_path.'/'.$domain.'/fullchain.pem');
                $sslPri = $this->__getSSLFile($cert_base_path.'/'.$domain.'/privkey.pem');

                $request = Dcdn::v20180115()->batchSetDcdnDomainCertificate();
                $result = $request
                    ->withDomainName($domain)
                    ->withCertName($saveCertName)
                    ->withCertType('upload')
                    ->withSSLProtocol('on')
                    ->withSSLPub($sslPub)
                    ->withSSLPri($sslPri)
                    ->withRegion('cn-shanghai')
                    ->debug(true)

                    ->request();

                return $result->toArray();
            } catch (FileNotFoundException $e) {
                Log::error($e->getMessage());

                throw new \Exception($domain);
            } catch (ClientException $e) {
                Log::error($e->getMessage());

                throw new \Exception($e->getMessage());
            } catch (ServerException $e) {
                Log::error($e->getMessage());

                throw new \Exception($e->getMessage());
            } catch (Exception $e) {
                Log::error($e->getMessage());

                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     * 根据关键字查找证书.
     *
     * @param string $keywords 关键字
     * @param bool   $force    是否强制调用阿里云 SDK 获取数据
     */
    public function list(string $keywords = null, bool $force = false): array
    {
        try {
            if (!$force && Storage::disk('local')->exists('domains.txt') && $contents = Storage::disk('local')->get('domains.txt')) {
                $domains = json_decode($contents, JSON_UNESCAPED_SLASHES && JSON_UNESCAPED_UNICODE);

                return $domains['CertInfos']['CertInfo'];
            } else {
                $request = Dcdn::v20180115()->describeDcdnHttpsDomainList();
                if ($keywords) {
                    $result = $request->withKeyword($keywords);
                }

                $result = $request->debug(true)->request();

                $records = $result->toArray();

                Storage::disk('local')->put('domains.txt', json_encode($records));

                return $records['CertInfos']['CertInfo'];
            }
        } catch (ClientException $e) {
            Log::error($e->getMessage());

            throw new \Exception($e->getMessage());
        } catch (ServerException $e) {
            Log::error($e->getMessage());

            throw new \Exception($e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 根据域名查询证书明细.
     *
     * @param string $domain 域名
     */
    public function findByDomain(string $domain): array
    {
        try {
            $request = Dcdn::v20180115()->describeDcdnDomainCertificateInfo();
            $result = $request->withDomainName($domain)->debug(true)->request();

            return $result->toArray();
        } catch (ClientException $e) {
            Log::error($e->getMessage());

            throw new \Exception($e->getMessage());
        } catch (ServerException $e) {
            Log::error($e->getMessage());

            throw new \Exception($e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw new \Exception($e->getMessage());
        }
    }
}
