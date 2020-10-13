<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc;

use LogicException;

class Method
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $returnType = '';

    /** @var string */
    public $callType = '';

    /** @var string */
    public const CALL_TYPE_STATIC = 'static';

    /** @var string */
    public const CALL_TYPE_INSTANCE = '';

    /** @var string[] */
    private const SUPPORT_CALL_TYPE = [
        self::CALL_TYPE_INSTANCE,
        self::CALL_TYPE_STATIC,
    ];

    public function __construct(string $name, string $returnType, string $callType = self::CALL_TYPE_INSTANCE)
    {
        if (!in_array($callType, self::SUPPORT_CALL_TYPE, true)) {
            throw new LogicException();
        }
        $this->name = $name;
        $this->returnType = $returnType;
        $this->callType = $callType;
    }
}
