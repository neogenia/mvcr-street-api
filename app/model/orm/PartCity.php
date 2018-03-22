<?php

namespace StreetApi\Model;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 */
class PartCity
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
     * @ORM\OneToMany(targetEntity="Street", mappedBy="partCity")
     */
	protected $streets;

	/**
	 * @ORM\ManyToOne(targetEntity="City", inversedBy="partCities", fetch="EAGER")
	 */
	protected $city;

}
