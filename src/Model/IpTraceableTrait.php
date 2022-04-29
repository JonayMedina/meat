<?php
/**
 * Created by PhpStorm.
 * User: rodmar
 * Date: 21/06/18
 * Time: 04:30 PM
 */

namespace App\Model;

use Gedmo\Mapping\Annotation as Gedmo;

trait IpTraceableTrait
{
	/**
	 * @var string $createdFromIp
	 *
	 * @Gedmo\IpTraceable(on="create")
	 * @ORM\Column(name="created_from_ip", type="string", length=45, nullable=true)
	 */
	private $createdFromIp;

	/**
	 * @var string $updatedFromIp
	 *
	 * @Gedmo\IpTraceable(on="update")
	 * @ORM\Column(name="updated_from_ip", type="string", length=45, nullable=true)
	 */
	private $updatedFromIp;

	/**
	 * @return string
	 */
	public function getCreatedFromIp()
	{
		return $this->createdFromIp;
	}

	/**
	 * @return string
	 */
	public function getUpdatedFromIp()
	{
		return $this->updatedFromIp;
	}
}
