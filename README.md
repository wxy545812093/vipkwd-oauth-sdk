# Vipkwd-OAuth-SDK

The SDK for VK-OAuth system.

## Installing.

Install library via composer:

```Php
composer require vipkwd/oauth-sdk
```

## Usage.

### 1. To initialize the SDK
```php
include_once('vendor/autoload.php');
use Vipkwd\OAuth\OAuth;
use Vipkwd\OAuth\Restful;
$OAuthInstance = new OAuth([
    'client_id'     => 'xxxx',
    'client_secret' => '',
    'response_type' => 'code',  // In the SDK, the default is `code`
    'scope'         => 'basic', // In the SDK, the default is `basic`
    'state'         => 'xyz',   // In the SDK, the default is `xyz`
    'redirect_uri'  => 'https://xxx.demo.com/vk-oauth/callback.php'
]) :Object;
```

### 2. Generate the web authorization login url ...
```php
$url = $OAuthInstance->getWebAuthorizeUrl() :String;

// $url: https://oauth.vipkwd.com/v1/oauth/authorize?client_id=%s&response_type=%s&scope=%s&state=%s&redirect_uri=%s
// <a href="{$url}">VK-OAuth Login</a>"
```

### 3. Redirect callback ...
>  Receives the oauth server redirect callback in `redirect_uri` page of the application side.

```php
/* /vk-oauth/callback.php */

// Receives the redirect with param `code` and `state`
$res = $OAuthInstance->callback():array|void;
```

> If `$res` type is array, it is the token block(you can use `$OAuthInstance->getTokenInfo` to get it too). 
> if `$res` is other type, indicating that it has an error, you can use `$OAuthInstance->except` to get the error message.

### 4. CURD with RESTful ...
```php
Restful::post(string $service, int $resourceId=0, array $data = []):array;
Restful::delete(string $service, int $resourceId, array $data = []):array;
Restful::put(string $service, int $resourceId, array $data = []):array;
Restful::get(string $service, int $resourceId, array $data = []):array;
```