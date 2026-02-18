<?php
namespace DigitalPoint\Cloudflare\Admin\View;
class AbstractView
{
	use Traits\Phrase;

	protected $params = [];
	protected $name = '';
	protected $return = null;

	public function __construct($name, $params = [])
	{
		$this->name = preg_replace('#[^a-z0-9\-]#i' ,'', $name);

		$plugin = explode('/', plugin_basename(__FILE__), 2);
		$this->params = call_user_func('\\DigitalPoint\\' . preg_replace('#^.*?\\\\(.*?)\\\\.*$#', '$1', __NAMESPACE__) . '\\Base\\Pub::getInstance')->applyFilters(
			str_replace('-', '_', $plugin[0]) . '_view_arguments',
			$params);

		$this->return = $this->getView();
	}

	protected function getTemplate()
	{
		$className = 'DigitalPoint\\' . preg_replace('#^.*?\\\\(.*?)\\\\.*$#', '$1', __NAMESPACE__) . '\\Admin\\Template\\' . str_replace('-', '', ucwords($this->name, '-'));

		if (!class_exists($className))
		{
			die ('<div id="message" class="error notice"><p>' . sprintf(esc_html($this->abstractPhrase('template_not_found')), '<strong>', '</strong>', esc_html($this->name), esc_html($className)) . '</p></div>');
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