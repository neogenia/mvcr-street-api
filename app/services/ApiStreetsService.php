<?php

namespace StreetApi\Services;

use Doctrine\Common\Collections\Criteria;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette\Object;
use Nette\Utils\Strings;
use StreetApi\Model\City;
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

	/** @var EntityRepository */
	private $cityRepository;

	/** @var array */
	private $tempArray = [];


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->streetRepository = $em->getRepository(Street::class);
		$this->partCityRepository = $em->getRepository(PartCity::class);
		$this->cityRepository = $em->getRepository(City::class);
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
		$streets = $this->streetRepository->findBy($criteria, ['title' => Criteria::ASC, 'id' => Criteria::ASC]);

		$data = [];
		foreach ($streets as $street) {
			if (!array_search($street->title, $this->tempArray)) {
				$this->tempArray[] = $street->title;
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

	public function getCities($title = null)
	{
		$data = [];
		$criteria = [];

		if ($title) {
			$criteria['title LIKE'] = '%'.$title.'%';
		}

		$cities = $this->cityRepository->findBy($criteria, ['title' => Criteria::ASC, 'id' => Criteria::ASC]);

		foreach ($cities as $city) {
			if (is_numeric($city->title)) {
				continue;
			}

			$data[] = [
				'cityId' => $city->id,
				'title' => Strings::capitalize($city->title),
				'code' => $city->code,
				'region' => Strings::capitalize($city->region->title),
				'country' => Strings::capitalize($city->region->country),
			];
		}

		return ['cities' => $data];
	}

	public function getCityParts($title = null)
	{
		$data = [];
		$criteria = [];

		if ($title) {
			$criteria['title LIKE'] = '%'.$title.'%';
		}

		$cities = $this->cityRepository->findBy($criteria, ['title' => Criteria::ASC, 'id' => Criteria::ASC]);
		$cityParts = $this->partCityRepository->findBy($criteria, ['title' => Criteria::ASC, 'id' => Criteria::ASC]);

		$cityPartsIndexed = array();
		foreach($cityParts as $cityPart) {
			$cityPartsIndexed[$cityPart->city->id][] = $cityPart;
		}

		foreach ($cities as $city) {
			if (is_numeric($city->title)) {
				continue;
			}

			if(!empty($cityPartsIndexed[$city->id])) {
				foreach($cityPartsIndexed[$city->id] as $partCity) {
					$data[] = [
						'cityId' => $city->id,
						'partCityId' => $partCity->id,
						'title' => Strings::capitalize($partCity->title),
						'cityTitle' => Strings::capitalize($city->title),
						'code' => $partCity->code,
						'region' => Strings::capitalize($city->region->title),
						'country' => Strings::capitalize($city->region->country),
					];
				}
			}
			else {
				$data[] = [
					'cityId' => $city->id,
					'title' => Strings::capitalize($city->title),
					'code' => $city->code,
					'region' => Strings::capitalize($city->region->title),
					'country' => Strings::capitalize($city->region->country),
				];
			}
		}

		return ['cityParts' => $data];
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
		$streets = $this->streetRepository->findBy($criteria, ['title' => Criteria::ASC, 'id' => Criteria::ASC]);
		foreach ($streets as $street) {
			if (!is_numeric($street->title)) {
				if (!array_search($street->title, $this->tempArray)) {
					$this->tempArray[] = $street->title;
					$data[] = [
						'streetId' => $street->id,
						'title' => Strings::capitalize($street->title),
						'code' => $street->code,
					];
				}
			}
		}
		$city = $this->cityRepository->findOneBy(['code' => $cityId]);

		return ['city' => $city ? $city->title : NULL, 'streets' => $data];
	}


	/**
	 * @param int $cityId
	 * @return array
	 */
	protected function streetWithRegions($cityId, $title = NULL)
	{
		$data = [];

		$partCities = $this->partCityRepository->findBy(['city.code' => $cityId], ['title' => Criteria::ASC, 'id' => Criteria::ASC]);
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
