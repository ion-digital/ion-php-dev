# Auto Loader

[![License: LGPL v3](https://img.shields.io/badge/License-LGPL%20v3-blue.svg)](https://www.gnu.org/licenses/lgpl-3.0)

Auto-Loader is a small, Composer-compatible and customizable auto-loading library for PHP.

### Why not Composer's Auto-loader?

The obvious question... 

Composer's auto-loading capabilities are actually recommended and can be used along-side this library - however, in certain 
circumstances they can fall a bit short...

This library was created to address those very specific situations. For instance, let's consider a scenario where you would like to
seperate your base source code from different source code bases that depend on different versions of PHP.

With composer, you're stuck with one base source code directory - with this library, you can specify multiple.


## Features

* Seperation of source directories and additional build directories.
* Version management (just edit __version.json__ or __composer.json__ with [SemVer](http://semver.org/) compatible version data and you're good to go).
* Class location caches (cached using PHP syntax - to take advantage of the PHP op-cache, if available).
* [PSR-0](https://www.php-fig.org/psr/psr-0/ "php-fig.org") and [PSR-4](https://www.php-fig.org/psr/psr-4/ "php-fig.org").
* Full control (you can turn features like debugging or caching on globally, or just for a single package).


## Getting Started

###As an included library, with Composer:

Make sure Composer is installed - if not, you can get it from [here](https://getcomposer.org/ "getcomposer.org").

First, you need to add _ion/auto-loader_ as a dependency in your _composer.json_ file.

To use the current stable version, add the following to download it straight from [here](https://packagist.org/ "packagist.org"):

```
"require": {
    "php": ">=5.6",
    "ion/auto-loader": "^0.4.0",
}
```

To use the bleeding edge (development) version, add the following:

```
"require": {
    "php": ">=7.2",
    "ion/auto-loader": "dev-default",	
},
"repositories": {
    {
      "type": "hg",
      "url": "https://bitbucket.org/justusmeyer/auto-loader/"
    }
}
```

Then run the following in the root directory of your project:

> php composer.phar install


###As an included library, without Composer:

Download a packaged version (in __.ZIP__ format), [here](https://bitbucket.org/justusmeyer/auto-loader/downloads/?tab=tags "bitbucket.org")

Unzip the package and make sure you include '_include.php_,' like so (assuming you unzipped the package into the relative path '_includes/wp-helper_'):

```
require_once( __DIR__ . '/includes/auto-loader/include.php' ); 
```


### Prerequisites

* Composer (_optional_)


## Built With

* [Composer](https://getcomposer.org/) - Dependency Management
* [Phing](https://www.phing.info) - Used to generate custom builds for various target PHP versions (5.6, 7.0, 7.1)
* [NetBeans](https://www.netbeans.org) - IDE

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://bitbucket.org/justusmeyer/auto-loader/downloads/?tab=tags "bitbucket.org"). 

## Authors

* **Justus Meyer** - *Initial work* - [BitBucket](https://bitbucket.org/justusmeyer), [Upwork](https://justusmeyer.com/upwork)

## License

This project is licensed under the LGPL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details.

