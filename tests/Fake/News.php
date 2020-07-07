<?php

declare(strict_types=1);

use CActiveRecord;

/**
 * class News
 */
class News extends CActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritDoc
     */
    public function relations()
    {
        return [
            'HeadImage' => [self::BELONGS_TO, 'NewsTopImage', 'News_id'],
            'Image' => [self::HAS_MANY, NewsTopImage::class, 'News_id'],
        ];
    }
}
