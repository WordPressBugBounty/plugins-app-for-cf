<?php

namespace DigitalPoint\Cloudflare\Traits\Repository;

trait WP
{
	/**
	 * @return array
	 */
	protected function getAdminEmails()
	{
		$admins = get_users(['role' => 'administrator']);

		$emails = [];
		foreach ($admins as $admin)
		{
			if ($admin->data->user_email)
			{
				$emails[] = ['email' => ['email' => $admin->data->user_email]];
			}
		}

		return $emails;
	}

	/**
	 * @return array
	 */
	protected function getAdminAreas($include = '')
	{
		$areas = [
			'/wp-admin' => $this->phrase('wordpress_admin')
		];

		if ($include == 'xmlrpc')
		{
			$areas['/xmlrpc.php'] = $this->phrase('wordpress_xml_rpc');
		}

		return $areas;
	}


	protected function addCron($jobKey, $expireDays, $id)
	{
		//See: https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
		$split = explode('_', $jobKey);
		if (wp_next_scheduled($split[0], [$split[1]]))
		{
			$this->cancelCron($jobKey);
		}
		wp_schedule_single_event(time() + (86400 * $expireDays), $split[0], [$split[1]]);
	}

	protected function cancelCron($jobKey)
	{
		//See: https://developer.wordpress.org/reference/functions/wp_clear_scheduled_hook/
		$split = explode('_', $jobKey);
		wp_clear_scheduled_hook($split[0], [$split[1]]);
	}

	public function verifyTurnstileResponse($response)
	{
		return $this->getApiClass()->postTurnstileSiteVerify($this->option('cfTurnstile.secretKey'), $response, !empty($_SERVER['REMOTE_ADDR']) ? filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) : ''); /* @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash */
	}

}