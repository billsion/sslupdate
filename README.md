# 基于阿里云 DCDN API 的 SSL 更新项目

## 首先需要生成证书文件

## 项目配置
1. 将 `.env.example` 重命名为 `.env` 文件，并将里面的 `AK` 填写为阿里云的 `accessKey` `SK` 填写为阿里云的 `accessSecretKey`。

2. `SSL_PATH` 用来填写证书存放目录。

## 命令
```php artisan ssl:list {keywords?} {--F|force}``` 命令用来显示指定 `AK` 和 `SK` 下的 SSL 证书

`keywords` 参数表示查询关键字
`--F|force` 参数用来表示是否走缓存，携带此参数表示不走缓存

```php artisan ssl:find {domain}``` 命令用来显示指定 `domain` 证书的详细信息

```php artisan ssl:update {domains?*}``` 命令用来更新证书

多个域名通过 ` ` 空格传递，不携带 `domains` 参数将遍历所有域名，对 `CertStatus` 为 `expired_soon` 且还有一周过期的域名进行 SSL 更新。