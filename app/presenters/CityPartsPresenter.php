<?php
namespace StreetApi\Presenters;

use StreetApi\Services\ApiStreetsService;

class CityPartsPresenter extends ApiPresenter
{
    /** @var ApiStreetsService @inject */
    public $apiStreetsService;


    public function read()
    {

        if ($code = $this->getApiParameter('cityOrCityPartnameByCode', FALSE)) {
            $this->sendJson($this->apiStreetsService->getCityOrPartCityNameByCode($code));
        }
		elseif ($code = $this->getApiParameter('code', FALSE)) {
			$whitelistedCityParts = $this->getApiParameter('whitelistedCityParts', false);
			$this->sendJson($this->apiStreetsService->getCityParts([
				'code' => $code,
				'whitelistedCityParts' => $whitelistedCityParts,
			]));
        } else {
            $title = trim($this->getApiParameter('title', FALSE));
            $zip = $this->getApiParameter('zip', FALSE);
			$limit = $this->getApiParameter('limit', FALSE);
			$whitelistedCityParts = $this->getApiParameter('whitelistedCityParts', false);
            $this->sendJson($this->apiStreetsService->getCityParts([
            	'title' => $title,
	            'zip' => $zip,
	            'limit' => $limit,
	            'whitelistedCityParts' => $whitelistedCityParts,
            ]));
        }
    }
}
