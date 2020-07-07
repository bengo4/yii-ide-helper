<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Bengo4\YiiIdeHelper\Database\Database;
use Bengo4\YiiIdeHelper\Converters\AddORMPhpDocConverter;

/**
 * class AddORMPhpDocConverterTest
 */
class AddORMPhpDocConverterTest extends TestCase
{
    /**
     * @test
     */
    public function 正常にPHPDocが付与できる()
    {
        $testPhpFilePath = __DIR__ . '/../Fake/News.php';

        $database = $this->createMock(Database::class);
        $database->method('getDBColumnData')->willReturn([
            [
                'name' => 'id',
                'type' => 'int'
            ],
            [
                'name' => 'name',
                'type' => 'string|null'
            ]
        ]);

        $nameSpaceConverter = new AddORMPhpDocConverter($database);
        $result = $nameSpaceConverter->convert($testPhpFilePath, file_get_contents($testPhpFilePath));

        $expect = <<<PHP
<?php

declare(strict_types=1);

use CActiveRecord;

/**
 * class News
 *
 * @property int \$id
 * @property string|null \$name
 * @property NewsTopImage|null \$HeadImage
 * @property NewsTopImage[] \$Image
 */
class News extends CActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function model(\$className = __CLASS__)
    {
        return parent::model(\$className);
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

PHP;

        $this->assertSame(
            preg_replace('(\r\n|\r|\n)', PHP_EOL, $expect),
            preg_replace('(\r\n|\r|\n)', PHP_EOL, $result)
        );
    }
}
