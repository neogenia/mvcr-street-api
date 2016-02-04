<?php

namespace StreetApi\Presenters;

use Nette;
use Nette\Http\IResponse;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	public function startup()
	{
		parent::startup();
		$request = $this->getHttpRequest();
		if ($this->context->parameters['apiKey']) {
			if ($request->getHeader('X-Api-Key') !== $this->context->parameters['apiKey']) {
				$this->error('Unauthorized', IResponse::S401_UNAUTHORIZED);
			}
		}
	}

}
