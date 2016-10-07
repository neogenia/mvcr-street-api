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
            $this->sendJson($this->apiStreetsService->getCityParts(['code' => $code]));
        } else {
            $title = trim($this->getApiParameter('title', FALSE));
            $limit = $this->getApiParameter('limit', FALSE);
            $this->sendJson($this->apiStreetsService->getCityParts(['title' => $title, 'limit' => $limit]));
        }
    }
}
