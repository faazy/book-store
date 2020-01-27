<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryOfferRule
 *
 * @ORM\Table(name="category_offer_rule")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryOfferRuleRepository")
 */
class CategoryOfferRule
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}

