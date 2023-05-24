<?php

declare(strict_types=1);

namespace Vipkwd\OAuth;

use Vipkwd\OAuth\Dependents\Session;
use Vipkwd\OAuth\Dependents\Http;
use \Exception;

class OAuth
{
	private $options = [];
	private $session;
	private $except = [];
	private $server = 'http://oauth.hosts.run';
	private $version = 'v1';
	private $sessionKey = 'vk-oauth';

	public function __construct(array $options)
	{
		$this->options = array_merge([
			'client_id' => '',
			'redirect_uri' => '',
			'client_secret' => '',
			'response_type' => 'code',
			'scope' => 'basic',
			'state' => 'xyz'
		], $options);

		// $this->session = Session::instance();
	}

	/**
	 * 生成OAuth2.0 Web认证地址
	 * 
	 * @param string|null $redirect_uri <$options.redirect_uri>
	 * @param string|null $scope <basic>
	 * @param string|null $state <xyz>
	 * 
	 * @return string
	 */
	public function getWebAuthorizeUrl(?string $redirect_uri = null, ?string $scope = null, ?string $state = null): string
	{
		$param = function ($value, $mapKey) {
			return $value ? $value : (isset($this->options[$mapKey]) ? $this->options[$mapKey] : '');
		};
		return implode('/', [
			$this->server,
			$this->version,
			sprintf(
				'oauth/authorize?client_id=%s&response_type=%s&scope=%s&state=%s&redirect_uri=%s',
				$param(null, 'client_id'),
				$param(null, 'response_type'),
				$param($scope, 'scope'),
				$param($state, 'state'),
				$param($redirect_uri, 'redirect_uri'),
			)
		]);
	}

	public function __get(string $property)
	{
		if (in_array($property, ['server', 'version', 'sessionKey', 'except'])) {
			return $this->$property;
		}
		return null;
	}

	/**
	 * 接收用户中心返回的授权码
	 * 
	 * @throw \Exception
	 * @return bool|array
	 */
	public function callback()
	{
		if (isset($_GET['code']) && isset($_GET['state']) && $_SERVER['REQUEST_URI'] && (strripos($_SERVER['HTTP_REFERER'] ?? null, $this->server) !== false)) {
			//将认证服务器返回的授权码从 URL 中解析出来
			$authorizationCode = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], 'code=') + 5, 40);

			try {
				// 步骤4 拿授权码去申请令牌
				return $this->oAuthToken($authorizationCode);
			} catch (Exception $e) {
				// return $this->except;
			}
		} else {
			$this->except = [
				"error" => "invalid_callback",
				"error_description" => "Authorization code doesn't exist or is invalid for the client"
			];
		}
	}

	/**
	 * 获取本地缓存token信息
	 * 
	 * @param string $authorize_code
	 * 
	 * @throw \Exception
	 * @return array
	 */
	public function getTokenInfo(): array
	{
		return $this->session->get($this->sessionKey);
	}

	public function resource($method = 'get', string $service, $data = [])
	{
		$tokenInfo = $this->getTokenInfo();
		$url = rtrim($this->server, '/') . '/' . rtrim($this->version, '/') . '/oauth/resource?access_token=' . ($tokenInfo['access_token'] ?? '');
		$header = ['service' => sprintf(trim($service, '/') . '/%s.json', $method)];

		switch (strtoupper($method)) {
			case "POST":
				return Http::post($url, $data, 'json', $header);
			case "DELETE":
				return Http::delete($url, $data, 'json', $header);
			case "PUT":
				return Http::put($url, $data, 'json', $header);
			case "GET":
				return Http::get($url, $data, $header);
			case "OPTIONS":
				return Http::get($url, $data, $header);
		}
		return null;
	}

	/**
	 * 远程获取token
	 * 
	 * @param string $authorize_code
	 * 
	 * @throw \Exception
	 * @return array
	 */
	private function oAuthToken(string $authorize_code): array
	{
		// 步骤4 拿授权码去申请令牌
		$response = Http::post(rtrim($this->server, '/') . '/' . rtrim($this->version, '/') . '/oauth/token', http_build_query([
			'grant_type' => 'authorization_code',
			'code' => $authorize_code,
			'redirect_uri' => $this->options['redirect_uri'],
		]), 'form', [
			'auth' => [$this->options['client_id'], $this->options['client_secret']],
		]);
		return $this->exceptionHandler($response, function (&$res) {
			$res['expires_time'] = time() + $res['expires_in'] - 5;
			ksort($res);
			// 将令牌缓存到 SESSION中，方便后续访问
			// $this->session->set($this->sessionKey, $res);
		});
	}

	private function exceptionHandler(array $response, callable $callback): array
	{
		if (isset($response['error']) && isset($response['error_description'])) {
			$this->except = $response;
			throw new Exception($response['error_description']);
		}
		$callback($response);
		return $response;
	}
}
