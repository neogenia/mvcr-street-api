<?php


namespace StreetApi\Presenters;


use StreetApi\Services\ApiStreetsService;

class StreetsPresenter extends ApiPresenter
{

	/** @var ApiStreetsService @inject */
	public $apiStreetsService;


	public function read()
	{
		$title = $this->getApiParameter('title', FALSE);
		if ($partCityId = $this->getApiParameter('partCityId', FALSE)) {
			$this->sendJson($this->apiStreetsService->getStreetsFromPartCity($partCityId, $title));
		} elseif ($cityId = $this->getApiParameter('cityId', FALSE)) {
			$this->sendJson($this->apiStreetsService->getStreetsFromCity($cityId, $title, $this->getApiParameter('includePartCities', FALSE)));
		} elseif ($code = $this->getApiParameter('code', FALSE)) {
			$this->sendJson($this->apiStreetsService->getStreetsFromCityOrPartCityByCode($code, $title));
		} else {
			$this->sendJson([
				'error' => 'Missing one of partCityId or cityId parameter.'
			]);
		}
	}

}
