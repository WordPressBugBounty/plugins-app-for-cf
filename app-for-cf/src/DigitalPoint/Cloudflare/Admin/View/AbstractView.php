<?php
namespace DigitalPoint\Cloudflare\Admin\View;
class AbstractView
{
	protected $params = [];
	protected $name = '';
	protected $return = null;

	public function __construct($name, $params = [])
	{
		$this->name = preg_replace('#[^a-z0-9\-]#i' ,'', $name);
		$this->params = \DigitalPoint\Cloudflare\Base\Pub::getInstance()->applyFilters('app_for_cf_view_arguments', $params);
		$this->return = $this->getView();
	}

	protected function getTemplate()
	{
		$className = 'DigitalPoint\Cloudflare\Admin\Template\\' . str_replace('-', '', ucwords($this->name, '-'));

		if (!class_exists($className))
		{
			/* translators: %1$s = <strong>, %2$s = </strong> */
			die ('<div id="message" class="error notice"><p>' . sprintf(esc_html__('Template not found: %1$s%3$s%2$s (%4$s).', 'app-for-cf'), '<strong>', '</strong>', esc_html($this->name), esc_html($className)) . '</p></div>');
		}

		$template = new $className($this->params);
		return $template->output();
	}

	public function getView()
	{
		return $this->getTemplate();
	}

	public function getReturn()
	{
		return $this->return;
	}

}