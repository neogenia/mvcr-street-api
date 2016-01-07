<?php

namespace StreetApi\Services;

use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use SimpleXMLElement;
use StreetApi\Model\City;
use StreetApi\Model\PartCity;
use StreetApi\Model\Region;
use StreetApi\Model\Street;
use Symfony\Component\Console\Helper\ProgressBar;


class ImportAddressService extends Object
{

	/** @var string */
	private $rootDir;

	/** @var EntityManager */
	private $em;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $regionRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $cityRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $streetRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $partCityRepository;


	public function __construct(EntityManager $em, $rootDir)
	{
		$this->rootDir = $rootDir;
		$this->em = $em;
		$this->regionRepository = $em->getRepository(Region::class);
		$this->cityRepository = $em->getRepository(City::class);
		$this->streetRepository = $em->getRepository(Street::class);
		$this->partCityRepository = $em->getRepository(PartCity::class);
	}


	/**
	 * @return string
	 */
	public function getRootDir()
	{
		return $this->rootDir;
	}


	/**
	 * @param SimpleXMLElement $xmlFile
	 * @param ProgressBar $progressBar
	 */
	public function import(SimpleXMLElement $xmlFile, ProgressBar $progressBar, $cityId = NULL)
	{
		foreach ($xmlFile->oblast as $region) {
			$regionEntity = $this->parseRegion($region, $progressBar);
			foreach ($region as $city) {
				if ($cityId && $city['kod'] != $cityId) {
					continue;
				}
				$cityEntity = $this->parseCity($city, $regionEntity);
				foreach ($city->cast as $partCity) {
					$partCityEntity = $this->parsePartCity($partCity, $cityEntity);
					foreach ($partCity->ulice as $street) {
						$this->parseStreet($street, $partCityEntity);
					}
				}
			}
			$progressBar->advance();
		}
	}


	/**
	 * @param array $region
	 * @param ProgressBar $progressBar
	 * @return Region|null
	 */
	protected function parseRegion($region, ProgressBar $progressBar)
	{
		$title = isset($region['nazev']) ? (string) $region['nazev'] : NULL;
		$country = isset($region['kraj']) ? (string) $region['kraj'] : NULL;
		$district = isset($region['okres']) ? (string) $region['okres'] : NULL;

		$progressBar->setMessage('<info>processing ' . $title . '</info>');

		$region = $this->regionRepository->findOneBy(['title' => $title]);
		if (!$region) {
			$region = new Region();
		}
		$region->title = $title;
		$region->country = $country;
		$region->district = $district;
		$this->em->persist($region);
		$this->em->flush();

		return $region;
	}


	/**
	 * @param array $city
	 * @param Region $region
	 * @return City|null
	 */
	protected function parseCity($city, Region $region)
	{
		$code = isset($city['kod']) ? (string) $city['kod'] : NULL;
		$title = isset($city['nazev']) ? (string) $city['nazev'] : NULL;
		$minZip = isset($city['MinPSC']) ? (string) $city['MinPSC'] : NULL;
		$maxZip = isset($city['MaxPSC']) ? (string) $city['MaxPSC'] : NULL;
		$menCount = isset($city['muzi']) ? (string) $city['muzi'] : NULL;
		$womenCount = isset($city['zeny']) ? (string) $city['zeny'] : NULL;

		$city = $this->cityRepository->findOneBy(['code' => $code]);
		if (!$city) {
			$city = new City();
			$city->code = $code;
		}
		$city->title = $title;
		$city->minZip = $minZip;
		$city->maxZip = $maxZip;
		$city->menCount = $menCount;
		$city->womenCount = $womenCount;
		$city->region = $region;
		$this->em->persist($city);
		$this->em->flush();

		return $city;
	}


	/**
	 * @param $partCity
	 * @param City $city
	 * @return PartCity|null
	 */
	protected function parsePartCity($partCity, City $city)
	{
		$code = isset($partCity['kod']) ? (string) $partCity['kod'] : NULL;
		$title = isset($partCity['nazev']) ? (string) $partCity['nazev'] : NULL;
		$minZip = isset($partCity['MinPSC']) ? (string) $partCity['MinPSC'] : NULL;
		$maxZip = isset($partCity['MaxPSC']) ? (string) $partCity['MaxPSC'] : NULL;

		$partCity = $this->partCityRepository->findOneBy(['code' => $code]);
		if (!$partCity) {
			$partCity = new PartCity();
			$partCity->code = $code;
		}
		$partCity->title = $title;
		$partCity->minZip = $minZip;
		$partCity->maxZip = $maxZip;
		$partCity->city = $city;

		$this->em->persist($partCity);
		$this->em->flush();

		return $partCity;
	}


	/**
	 * @param array $street
	 * @param PartCity $partCity
	 */
	protected function parseStreet($street, PartCity $partCity)
	{
		$code = isset($street['kod']) ? (string) $street['kod'] : NULL;
		$title = isset($street['nazev']) ? (string) $street['nazev'] : NULL;

		$street = $this->streetRepository->findOneBy(['code' => $code]);
		if (!$street) {
			$street = new Street();
			$street->code = $code;
		}
		$street->title = $title;
		$street->partCity = $partCity;

		$this->em->persist($street);
		$this->em->flush();
	}

}
