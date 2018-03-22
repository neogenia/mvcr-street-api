<?php

namespace StreetApi\Model;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 */
class City
{

	use \Kdyby\Doctrine\Entities\MagicAccessors;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $title;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $code;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $minZip;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $maxZip;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $menCount;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $womenCount;

    /**
     * @ORM\OneToMany(targetEntity="PartCity", mappedBy="city")
     */
	protected $partCities;

	/**
	 * @ORM\ManyToOne(targetEntity="Region", inversedBy="cities", fetch="EAGER")
	 */
	protected $region;

}
