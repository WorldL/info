<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InfoRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Info
{

    use TimestampableEntity;
    use SoftDeleteableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $ratio;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=6, nullable=true)
     */
    private $lbs_lat;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=6, nullable=true)
     */
    private $lbs_lng;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private $fav_count;

    /**
     * @ORM\Column(type="integer")
     */
    private $col_count;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lbs_title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRatio(): ?string
    {
        return $this->ratio;
    }

    public function setRatio(string $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    public function getLbsLat()
    {
        return $this->lbs_lat;
    }

    public function setLbsLat($lbs_lat): self
    {
        $this->lbs_lat = $lbs_lat;

        return $this;
    }

    public function getLbsLng()
    {
        return $this->lbs_lng;
    }

    public function setLbsLng($lbs_lng): self
    {
        $this->lbs_lng = $lbs_lng;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getFavCount(): ?int
    {
        return $this->fav_count;
    }

    public function setFavCount(int $fav_count): self
    {
        $this->fav_count = $fav_count;

        return $this;
    }

    public function getColCount(): ?int
    {
        return $this->col_count;
    }

    public function setColCount(int $col_count): self
    {
        $this->col_count = $col_count;

        return $this;
    }

    public function getLbsTitle(): ?string
    {
        return $this->lbs_title;
    }

    public function setLbsTitle(?string $lbs_title): self
    {
        $this->lbs_title = $lbs_title;

        return $this;
    }
}
