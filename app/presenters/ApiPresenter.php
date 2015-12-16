<?php

namespace StreetApi\Presenters;

use Nette\Application\AbortException;
use StreetApi\InvalidStateException;
use Tracy\Debugger;


class ApiPresenter extends BasePresenter
{


	public function actionRead()
	{
		$this->callMethod('read');
	}


	public function actionCreate()
	{
		$this->callMethod('create');
	}


	public function actionUpdate()
	{
		$this->callMethod('update');
	}


	public function actionDelete()
	{
		$this->callMethod('delete');
	}


	protected function getApiParameter($key, $need = TRUE)
	{
		$query = $this->getParameter('query');
		if (!isset($query->$key)) {
			if ($need) {
				throw new InvalidStateException('Missing ' . $key . ' parameter.');
			}

			return NULL;
		}

		return $query->$key;
	}


	private function callMethod($method)
	{
		try {

			call_user_func([$this, $method]);

		} catch (AbortException $e) {
			if ($e->getCode() === 0) {
				throw $e;
			}

			$this->sendJson([
				'status' => $e->getCode(),
				'message' => 'AbortException: ' . $e->getMessage(),
			]);
		} catch (\Exception $e) {
			Debugger::log($e);
			$this->sendJson([
				'status' => 500,
				'message' => get_class($e) . ': ' . $e->getMessage(),
			]);
		}
	}

}
