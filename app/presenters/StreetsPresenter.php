<?php


namespace StreetApi\Presenters;


use StreetApi\Services\ApiStreetsService;

class StreetsPresenter extends ApiPresenter
{

	/** @var ApiStreetsService @inject */
	public $apiStreetsService;


	public function read()
	{
		if ($partCityId = $this->getApiParameter('partCityId', FALSE)) {
			$this->sendJson($this->apiStreetsService->getStreetsFromPartCity($partCityId));
		} elseif ($cityId = $this->getApiParameter('cityId', FALSE)) {
			$this->sendJson($this->apiStreetsService->getStreetsFromCity($cityId, $this->getApiParameter('includePartCities', FALSE)));
		} else {
			$this->sendJson([
				'error' => 'Missing one of partCityId or cityId parameter.'
			]);
		}
	}

}
