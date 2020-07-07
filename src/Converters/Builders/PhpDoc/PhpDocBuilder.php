<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc;

use PhpParser\Comment;

abstract class PhpDocBuilder
{
    /**
     * @var Comment
     */
    protected $doc;

    /**
     * @var string[]
     */
    protected $comments;

    /**
     * @param Comment $doc
     * @return void
     */
    public function setComment(Comment $doc): void
    {
        $this->doc = $doc;

        $this->comments = explode(PHP_EOL, $this->doc->getText());
    }
}
