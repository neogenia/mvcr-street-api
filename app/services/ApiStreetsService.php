<?php

namespace StreetApi\Services;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette\Object;
use Nette\Utils\Strings;
use StreetApi\Model\PartCity;
use StreetApi\Model\Street;


class ApiStreetsService extends Object
{

	/** @var EntityManager */
	private $em;

	/** @var EntityRepository */
	private $streetRepository;

	/** @var EntityRepository */
	private $partCityRepository;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->streetRepository = $em->getRepository(Street::class);
		$this->partCityRepository = $em->getRepository(PartCity::class);
	}


	/**
	 * @param int $partCityId
	 * @return array
	 */
	public function getStreetsFromPartCity($partCityId)
	{
		$streets = $this->streetRepository->findBy(['partCity' => $partCityId]);

		$data = [];
		foreach ($streets as $street) {
			$data[] = [
				'streetId' => $street->id,
				'title' => Strings::capitalize($street->title),
				'code' => $street->code,
			];
		}

		return ['streets' => $data];
	}


	/**
	 * @param $cityId
	 * @param bool|null $includeRegions
	 */
	public function getStreetsFromCity($cityId, $includePartCities = NULL)
	{
		if ($includePartCities) {
			return $this->streetWithRegions($cityId);
		} else {
			return $this->streetWithoutRegions($cityId);
		}
	}


	/**
	 * @param int $cityId
	 * @return array
	 */
	protected function streetWithoutRegions($cityId)
	{
		$data = [];

		$streets = $this->streetRepository->findBy(['partCity.city' => $cityId]);
		foreach ($streets as $street) {
			$data[] = [
				'streetId' => $street->id,
				'title' => Strings::capitalize($street->title),
				'code' => $street->code,
			];
		}

		return ['streets' => $data];
	}


	/**
	 * @param int $cityId
	 * @return array
	 */
	protected function streetWithRegions($cityId)
	{
		$data = [];

		$partCities = $this->partCityRepository->findBy(['city' => $cityId]);
		foreach ($partCities as $key => $partCity) {
			$data[$key] = [
				'title' => $partCity->title,
				'code' => $partCity->code,
				'minZip' => $partCity->minZip,
				'maxZip' => $partCity->maxZip,
			];
			$data[$key] += $this->getStreetsFromPartCity($partCity->id);
		}

		return ['partCities' => $data];

	}

}
