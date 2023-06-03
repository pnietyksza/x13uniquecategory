<?php

namespace PrestaShop\Module\X13uniquecategory\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class UniqueCategory
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="is_unique_category", type="boolean")
     */
    private $isUnique;

    /**
     * @var int
     *
     * @ORM\Column(name="id_category", type="integer")
     */
    private $idCategory;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * @return int
     */
    public function getIdCategory()
    {
        return $this->idCategory;
    }

    /**
     * @param int $isUnique
     *
     * @return int
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;

        return $this;
    }

    /**
     * @param int $isUnique
     *
     * @return int
     */
    public function setIdCategory($idCategory)
    {
        $this->idCategory = $idCategory;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id_category' => $this->getIdCategory(),
            'is_unique_category' => $this->getIsUnique(),
            'id' => $this->getId(),
        ];
    }
}
