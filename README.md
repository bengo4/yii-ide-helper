# Yii IDE Helper

Generate Yii ActiveRecord PhpDoc Property.

Getting properties with ActiveRecord is a magic method.
Therefore, it can not be interpreted well by static analysis such as IDE.

So this tools will give you a PHPDoc for interpretation.

```
Support Database: MySQL
PHP Version: >= 7.1
Yii Framework: 1.1 => 2
```

# installation

```
git clone git@github.com:bengo4/yii-ide-helper.git
```

# Usage

```
composer install

/bin/ormdoc --path=../test-project/models/

```

If your database has emulated mode enabled.

```
/bin/ormdoc --path=../test-project/models/ --mode=1
```

## Before

```
/**
 * class News
 */
class News extends CActiveRecord
```

## After

```
/**
 * class News
 *
 * @property int $id
 * @property string|null $name
 * @property NewsTopImage|null $HeadImage
 * @property NewsTopImage[] $Image
 */
class News extends CActiveRecord
```

# Environment Value

Use environment variables to connect to the DB.

You should add the following to your `.env` file.

# Must

```
DATABASE_NAME=
```

# Available value

```
DATABASE_NAME=
DATABASE_HOST=
DATABASE_PORT=
DATABASE_USER=
DATABASE_PASSWORD=
```

## Default value

```
DATABASE_HOST=127.0.0.1
DATABASE_PORT=3306
DATABASE_USER=root
DATABASE_PASSWORD=root
```
