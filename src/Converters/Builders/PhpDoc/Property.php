<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc;

class Property
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $type = '';

    /**
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
}
