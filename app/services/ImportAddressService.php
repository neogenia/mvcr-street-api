<?php

namespace StreetApi\Services;

use Kdyby\Doctrine\EntityManager;
use Nette\Object;
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


	public function getRootDir()
	{
		return $this->rootDir;
	}


	public function import($xmlFile, ProgressBar $progressBar)
	{
		foreach ($xmlFile->oblast as $region) {
			$regionEntity = $this->parseRegion($region, $progressBar);
			foreach ($region as $city) {
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


	protected function parseRegion($region, ProgressBar $progressBar)
	{
		$title = (string) $region['nazev'];
		$country = (string) $region['kraj'];
		$district = (string) $region['okres'];

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


	protected function parseCity($city, Region $region)
	{
		$code = (string) $city['kod'];
		$title = (string) $city['nazev'];
		$minZip = (string) $city['MinPSC'];
		$maxZip = (string) $city['MaxPSC'];
		$menCount = (string) $city['muzi'];
		$womenCount = (string) $city['zeny'];

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


	protected function parsePartCity($partCity, City $city)
	{
		$code = (string) $partCity['kod'];
		$title = (string) $partCity['nazev'];
		$minZip = (string) $partCity['MinPSC'];
		$maxZip = (string) $partCity['MaxPSC'];

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


	protected function parseStreet($street, PartCity $partCity)
	{
		$code = (string) $street['kod'];
		$title = (string) $street['nazev'];

		$street = $this->streetRepository->findOneBy(['code' => $code]);
		if (!$street) {
			$street = new Street();
			$street->code = $code;
		}
		$street->title = $title;
		$street->city = $partCity;

		$this->em->persist($street);
		$this->em->flush();
	}

}
