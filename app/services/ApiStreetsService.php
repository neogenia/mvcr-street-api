<?php

namespace StreetApi\Services;

use Doctrine\Common\Collections\Criteria;
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
	public function getStreetsFromPartCity($partCityId, $title = NULL)
	{
		if ($title) {
			$criteria = ['partCity' => $partCityId, 'title LIKE' => '%'.$title.'%'];
		} else {
			$criteria = ['partCity' => $partCityId];
		}
		$streets = $this->streetRepository->findBy($criteria, ['title' => Criteria::ASC]);

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
	public function getStreetsFromCity($cityId, $title = NULL, $includePartCities = NULL)
	{
		if ($includePartCities) {
			return $this->streetWithRegions($cityId, $title);
		} else {
			return $this->streetWithoutRegions($cityId, $title);
		}
	}


	/**
	 * @param int $cityId
	 * @return array
	 */
	protected function streetWithoutRegions($cityId, $title = NULL)
	{
		$data = [];

		if ($title) {
			$criteria = ['partCity.city.code' => $cityId, 'title LIKE' => '%'.$title.'%'];
		} else {
			$criteria = ['partCity.city.code' => $cityId];
		}
		$streets = $this->streetRepository->findBy($criteria, ['title' => Criteria::ASC]);
		foreach ($streets as $street) {
			if (!is_numeric($street->title)) {
				$data[] = [
					'streetId' => $street->id,
					'title' => Strings::capitalize($street->title),
					'code' => $street->code,
				];
			}
		}

		return ['streets' => $data];
	}


	/**
	 * @param int $cityId
	 * @return array
	 */
	protected function streetWithRegions($cityId, $title = NULL)
	{
		$data = [];

		$partCities = $this->partCityRepository->findBy(['city' => $cityId], ['title' => Criteria::ASC]);
		foreach ($partCities as $key => $partCity) {
			if (!is_numeric($partCity->title)) {
				$data[$key] = [
					'title' => $partCity->title,
					'code' => $partCity->code,
					'minZip' => $partCity->minZip,
					'maxZip' => $partCity->maxZip,
				];
			}
			$data[$key] += $this->getStreetsFromPartCity($partCity->id, $title);
		}

		return ['partCities' => $data];

	}

}
