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
	const CITY_PARTS_INCLUDED = [
		6257, // Praha
		347, // Brno
		3520, // Ostrava
		3492, // Opava
		2533, // Liberec
		3758, // Plzeň
		1336, // Hradec Králové
		3615, // Pardubice
		5559, // Ústí n. L.
	];

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
			if (is_numeric($street->title) || array_key_exists($street->title, $this->tempArray)) {
				continue;
			}

			$this->tempArray[$street->title] = 1;
			$data[] = [
				'streetId' => $street->id,
				'title' => Strings::capitalize($street->title),
				'code' => $street->code,
			];
		}

		$partCity = $this->partCityRepository->findOneBy(['id' => $partCityId]);

		$partCityTitle = null;
		if ($partCity && $partCity->city) {
			$partCityTitle = $partCity->city->title;

			if ($partCity->city->title !== $partCity->title) {
				$partCityTitle .= ' - ' . $partCity->title;
			}
		}

		return [
			'city' => $partCityTitle,
			'streets' => $data,
		];
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
	 *
	 * @param array $filter
	 * @return array
	 */
	public function getCities(array $filter = array())
	{
		$data = [];
		$criteria = [];

		if ($filter['title']) {
			$criteria['title LIKE'] = '%'.$filter['title'].'%';
		}

		$limit = is_numeric($filter['limit']) ? $filter['limit'] : 0;

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

			if(count($data) == $limit) {
				break;
			}
		}

		return ['cities' => $data];
	}

	/**
	 *
	 * @param array $filter
	 * @return array
	 */
	public function getCityParts(array $filter = array())
	{
		$data = [];
		$criteria = [];
		$excludeCityParts = isset($filter['exclude']) ? $filter['exclude'] : false;

		if (!empty($filter['title'])) {
			$criteria['title LIKE'] = '%'.$filter['title'].'%';
		}

		if (!empty($filter['code'])) {
			$criteria['code'] = $filter['code'];
		}

		if (!empty($filter['zip'])) {
			$criteria['minZip <='] = $filter['zip'];
			$criteria['maxZip >='] = $filter['zip'];
		}

		$limit = !empty($filter['limit']) ? $filter['limit'] : 0;

		$cities = $this->cityRepository->findBy($criteria, ['title' => Criteria::ASC, 'id' => Criteria::ASC]);
		$cityIds = [];
		foreach ($cities as $city) {
			if (!$excludeCityParts || in_array($city->id, self::CITY_PARTS_INCLUDED)) {
				$cityIds[] = $city->id;
			}
		}

		$cityPartsAll = $this->partCityRepository->findBy(['city' => $cityIds], ['title' => Criteria::ASC, 'id' => Criteria::ASC]);

		$cityPartsIndexed = array();
		foreach($cityPartsAll as $cityPart) {
			$cityPartsIndexed[$cityPart->city->id][] = $cityPart;
		}

		// return all city parts for selected cities
		$usedCityParts = [];
		foreach ($cities as $city) {
			if (is_numeric($city->title)) {
				continue;
			}
			if (!isset($cityPartsIndexed[$city->id])) {
				$data[] = [
					'cityId' => $city->id,
					'title' => Strings::capitalize($city->title),
					'code' => $city->code,
					'region' => Strings::capitalize($city->region->title),
					'district' => Strings::capitalize($city->region->district),
					'country' => Strings::capitalize($city->region->country),
				];
				if (count($data) == $limit) {
					break;
				}
			}
			else {
				foreach ($cityPartsIndexed[$city->id] as $cityPart) {
					$data[] = [
						'cityId' => $city->id,
						'partCityId' => $cityPart->id,
						'title' => Strings::capitalize($cityPart->title),
						'cityTitle' => Strings::capitalize($city->title),
						'code' => $cityPart->code,
						'region' => Strings::capitalize($city->region->title),
						'district' => Strings::capitalize($city->region->district),
						'country' => Strings::capitalize($city->region->country),
					];
					$usedCityParts[] = $cityPart->id;
					if (count($data) == $limit) {
						break 2;
					}
				}
			}
		}

		if ($excludeCityParts) {
			$criteria['city'] = self::CITY_PARTS_INCLUDED;
		}
		$cityParts = $this->partCityRepository->findBy($criteria, ['title' => Criteria::ASC, 'id' => Criteria::ASC]);
		// return selected cityparts not used in first foreach
		foreach ($cityParts as $cityPart) {
			if (in_array($cityPart->id, $usedCityParts)) {
				continue;
			}
			$city = $cityPart->city;
			$data[] = [
				'cityId' => $city->id,
				'partCityId' => $cityPart->id,
				'title' => Strings::capitalize($cityPart->title),
				'cityTitle' => Strings::capitalize($city->title),
				'code' => $cityPart->code,
				'region' => Strings::capitalize($city->region->title),
				'district' => Strings::capitalize($city->region->district),
				'country' => Strings::capitalize($city->region->country),
			];
			$usedCityParts[] = $cityPart->id;
			if(count($data) == $limit) {
				break;
			}
		}

		return ['cityParts' => $data];
	}

	public function getCityOrPartCityNameByCode($code)
	{
		$partCity = $this->partCityRepository->findOneBy(['code' => $code]);
		if ($partCity) {
			return [
				'name' => $partCity->title
			];
		}

		$city = $this->cityRepository->findOneBy(['code' => $code]);
		if ($city) {
			return [
				'name' => $city->title
			];
		}

		return [
			'name' => []
		];
	}

	public function getStreetsFromCityOrPartCityByCode($code, $title)
	{
		$partCity = $this->partCityRepository->findOneBy(['code' => $code]);
		if ($partCity) {
			return $this->getStreetsFromPartCity($partCity->id, $title);
		}

		$city = $this->cityRepository->findOneBy(['code' => $code]);
		if ($city) {
			return $this->getStreetsFromCity($city->code, $title);
		}

		return [
			'streets' => []
		];
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
			if (is_numeric($street->title) || array_key_exists($street->title, $this->tempArray)) {
				continue;
			}

			$this->tempArray[$street->title] = 1;
			$data[] = [
				'streetId' => $street->id,
				'title' => Strings::capitalize($street->title),
				'code' => $street->code,
			];
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
