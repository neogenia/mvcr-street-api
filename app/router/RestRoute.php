<?php

namespace StreetApi;

use Nette;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Http\Request as HttpRequest;
use Nette\Object;
use Nette\Utils\Strings;


class RestRoute extends Object implements IRouter
{
	const HTTP_HEADER_OVERRIDE = 'X-HTTP-Method-Override';
	const QUERY_PARAM_OVERRIDE = '__method';

	/** @var */
	private $prefix;

	/** @var */
	private $availableResource;

	/** @var */
	private $module;


	public function __construct($prefix, $availableResource, $module)
	{
		$this->prefix = trim($prefix, '/') . '/';
		$this->availableResource = (array) $availableResource;
		$this->module = $module;
	}


	public function match(Nette\Http\IRequest $httpRequest)
	{
		$url = $httpRequest->getUrl();
		$basePath = Strings::replace($url->getBasePath(), '/\//', '\/');
		$cleanPath = Strings::replace($url->getPath(), "/^{$basePath}/", '');

		$pathRegExp = '#' . preg_quote($this->prefix, '#')
			. '(' . implode('|', $this->availableResource) . ')/?$#';

		$matches = Strings::match($cleanPath, $pathRegExp);
		if (!$matches) {
			return NULL;
		}


		$presenter = self::path2presenter($matches[1]);
		$params = [
			'action' => $this->detectAction($httpRequest),
			'query' => $this->readInput(),
		];

		$request = new Request(
			$presenter,
			$this->detectMethod($httpRequest),
			$params,
			$httpRequest->getPost()
		);

		return $request;
	}


	public function constructUrl(Request $appRequest, Nette\Http\Url $refUrl)
	{
		return NULL;
	}


	/**
	 * @return string|null
	 */
	protected function readInput()
	{
		$input = file_get_contents('php://input');
		if (empty($input)) {
			return $input;
		} else {
			return Nette\Utils\Json::decode($input);
		}
	}


	/**
	 * @return string
	 */
	protected function detectAction(HttpRequest $request)
	{
		$method = $this->detectMethod($request);

		switch ($method) {
			case 'GET': return 'read';
			case 'POST': return 'create';
			case 'PUT': return 'update';
			case 'DELETE': return 'delete';
		}

		throw new InvalidStateException('Method ' . $method . ' is not allowed.');
	}


	/**
	 * @param  HttpRequest $request
	 * @return string
	 */
	protected function detectMethod(HttpRequest $request)
	{
		$requestMethod = $request->getMethod();
		if ($requestMethod !== 'POST') {
			return $request->getMethod();
		}

		$method = $request->getHeader(self::HTTP_HEADER_OVERRIDE);
		if (isset($method)) {
			return strtoupper($method);
		}

		$method = $request->getQuery(self::QUERY_PARAM_OVERRIDE);
		if (isset($method)) {
			return strtoupper($method);
		}

		return $requestMethod;
	}


	/**
	 * dash-and-dot-separated -> PascalCase:Presenter name.
	 * @param  string
	 * @return string
	 */
	private static function path2presenter($s)
	{
		$s = strtolower($s);
		$s = preg_replace('#([.-])(?=[a-z])#', '$1 ', $s);
		$s = ucwords($s);
		$s = str_replace('. ', ':', $s);
		$s = str_replace('- ', '', $s);
		return $s;
	}

}
