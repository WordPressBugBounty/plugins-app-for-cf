<?php

namespace DigitalPoint\Cloudflare\Format\Table;
abstract class AbstractTable extends \WP_List_Table
{
	public function __construct(array $args = [])
	{
		// because this isn't hacky, right?  lol
		if (!empty($_GET['action']) && in_array($_GET['action'], ['enable', 'disable', 'delete'])) /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		{
			$_SERVER['REQUEST_URI'] = remove_query_arg(['id', 'action', '_wpnonce'], sanitize_text_field($_SERVER['REQUEST_URI'])); /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash */
		}

		parent::__construct($args);
	}

	protected function display_tablenav($which)
	{
		if ($this->get_bulk_actions())
		{
			parent::display_tablenav($which);
		}
	}

	protected function getCurrentUrl()
	{
		return set_url_scheme(sanitize_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])); /* @phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash */
	}

	public function ajax_user_can()
	{
		return current_user_can('manage_options');
	}

	public function prepare_items()
	{
		global $totals, $status;

		$columns = $this->get_columns();
		$this->_column_headers = [$columns];

		if (!empty($this->_args['rules']))
		{
			$this->items = $this->_args['rules'];
		}
		elseif (!empty($this->_args['logs']))
		{
			$this->items = $this->_args['logs'];
		}

		$this->set_pagination_args([
			'total_items' => ((!empty($totals[$status]) ? $totals[$status] : 0) + 0),
			'per_page' => 1000,
		]);
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}
		$actions = [];

		return $this->row_actions($actions);
	}

	protected function row_actions($actions, $always_visible = false)
	{
		return '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>'; /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
	}

	protected function column_cb($item)
	{
		if (\DigitalPoint\Cloudflare\Helper\Api::$version)
		{
			/* translators: %s = Item being selected */
			echo wp_kses("<label class='screen-reader-text' for='checkbox_" . $item['id'] . "' >" . sprintf(esc_html__('Select %s'), !empty($item['description']) ? $item['description'] : '') . "</label>" /* @phpcs:ignore WordPress.WP.I18n.MissingArgDomain */
				. "<input type='checkbox' name='checked[]' value='" . esc_attr( $item['id'] ) . "' id='checkbox_" . $item['id'] . "' />",
				[
					'label' => [
						'class' => [],
						'for' => []
					],
					'input' => [
						'type' => [],
						'name' => [],
						'value' => [],
						'id' => []
					]
				]);
		}
	}

	abstract protected function phrase($key);

}