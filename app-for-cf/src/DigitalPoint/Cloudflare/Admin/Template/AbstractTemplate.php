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
		call_user_func('\\DigitalPoint\\' . preg_replace('#^.*?\\\\(.*?)\\\\.*$#', '$1', __NAMESPACE__) . '\\Helper\\WordPress::addAsset', $type);
	}
}