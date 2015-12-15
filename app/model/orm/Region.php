<?php

namespace StreetApi\Model;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 */
class Region
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
     * @ORM\OneToMany(targetEntity="City", mappedBy="region")
     */
	protected $cities;


}
