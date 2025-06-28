<?php

namespace DigitalPoint\Cloudflare\Http;

/**
 * Not a true promise system like Guzzle has, but allows parallel HTTP requests across multiple platforms
 * (WordPress doesn't use Guzzle or promises, so have to work within the bounds of the lowest common denominator).
 */
class Promise
{
	public $response = null;

	public $method = null;
	public $url = null;
	public $options = [];

	public function __construct($method, $url, array $options = [])
	{
		$this->method = strtoupper($method);
		$this->url = $url;
		$this->options = $options;
	}
}