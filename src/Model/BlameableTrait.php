<?php
/**
 * Created by PhpStorm.
 * User: rodmar
 * Date: 21/06/18
 * Time: 04:16 PM
 */

namespace App\Model;

use Gedmo\Mapping\Annotation as Gedmo;

trait BlameableTrait
{
	/**
	 * @var string $createdBy
	 *
	 * @Gedmo\Blameable(on="create")
	 * @ORM\Column(name="created_by", type="string", nullable=true)
	 */
	private $createdBy;

	/**
	 * @var string $updatedBy
	 *
	 * @Gedmo\Blameable(on="update")
	 * @ORM\Column(name="updated_by", type="string", nullable=true)
	 */
	private $updatedBy;

	/**
	 * @return string
	 */
	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	/**
	 * @return string
	 */
	public function getUpdatedBy()
	{
		return $this->updatedBy;
	}
}
