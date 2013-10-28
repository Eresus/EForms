<?php
/**
 * Заглушки встроенных классов Eresus
 *
 * @package Eresus
 * @subpackage Tests
 */

use Mekras\TestDoubles\UniversalStub;
use Mekras\TestDoubles\MockFacade;

/**
 * Заглушка для класса Plugin
 *
 * @package Eresus
 * @subpackage Tests
 */
class Plugin extends UniversalStub
{
}

/**
 * Заглушка для класса TPlugin
 *
 * @package Eresus
 * @subpackage Tests
 */
class TPlugin extends UniversalStub
{
}

/**
 * Заглушка для класса Eresus_Kernel
 *
 * @package Eresus
 * @subpackage Tests
 */
class Eresus_Kernel extends MockFacade
{
}

/**
 * Заглушка для класса Eresus_CMS
 *
 * @package Eresus
 * @subpackage Tests
 */
class Eresus_CMS extends MockFacade
{
}
