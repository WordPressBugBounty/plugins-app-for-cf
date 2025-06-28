<?php
namespace DigitalPoint\Cloudflare\Admin\Template;
abstract class AbstractTemplate
{
	protected $params = [];
	public function __construct($params)
	{
		$this->params = $params;
	}
	abstract protected function template();

	public function output()
	{
		return $this->template();
	}

	protected function addAsset($type = 'css')
	{
		\DigitalPoint\Cloudflare\Helper\WordPress::addAsset($type);
	}

}