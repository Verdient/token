# 令牌
用于生成及解析认证令牌
## 安装
```bash
composer require verdient/token
```
## 生成秘钥
```php
use Verdient\token\Token;

/**
 * 秘钥
 */
$key = '***';

/**
 * 令牌有效期(s)
 * 默认为2592000，30天
 */
$duration = 2592000;

/**
 * 代价，代价值越大，生成的令牌越长，安全性越高，速度越慢
 * 默认为10
 */
$cost = 10;

$token = new Token([
	'key' => $key,
	'duration' => $duration,
	'cost' => $cost
]);

/**
 * 用于认证的关键信息
 * 可以是用户ID或其他用户唯一的值
 */
$identity = 1;

/*
 * $duration和$cost可以作为可选参数传入生成函数
 * 用于覆盖整全局的配置
 */
$tokenString = $token->generate($identity, $duration, $cost);
```
## 解析秘钥
```php
$identity = $token->parse($tokenString);
```