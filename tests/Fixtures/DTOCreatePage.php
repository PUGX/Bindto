<?php

namespace OB\Fixtures;

use Symfony\Component\Validator\Constraints as Assert;

class DTOCreatePage
{
    /**
     * @Assert\NotBlank(groups={"POST"})
     * @Assert\Length(
     *      min = 2,
     *      max = 50)
     *
     */
    public $title;

    /**
     * @Assert\NotNull(groups={"POST"})
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 10,
     *      max = 500)
     */
    public $body;

    /**
     * @Assert\NotBlank(groups={"POST"})
     * @Assert\Length(
     *      min = 2,
     *      max = 50)
     */
    public $seoTitle;

    /**
     * @Assert\NotBlank(groups={"POST"})
     */
    public $seoDescription;
}