<?php
namespace DigitalPoint\Cloudflare\Admin\Controller\Advanced;
abstract class Cloudflare extends \DigitalPoint\Cloudflare\Admin\Controller\AbstractController
{
	public function actionR2()
	{
		$viewParams = [];
		return $this->view('r2', $viewParams);
	}

	public function actionMultisiter2()
	{
		$viewParams = [];
		return $this->view('MultisiteR2', $viewParams);
	}
}