<?php
namespace StreetApi\Presenters;

use StreetApi\Services\ApiStreetsService;

class CitiesPresenter extends ApiPresenter
{
    /** @var ApiStreetsService @inject */
    public $apiStreetsService;


    public function read()
    {
        $title = $this->getApiParameter('title', FALSE);

        $this->sendJson($this->apiStreetsService->getCities($title));
    }
}
