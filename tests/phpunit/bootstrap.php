<?php
/**
 * Стартовый файл тестов
 *
 * @copyright 2011, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Михаил Красильников <mihalych@vsepofigu.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо (по вашему выбору) с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 *
 * @package E-Forms
 * @subpackage Tests
 */

/**
 * Путь к папке исходные кодов
 */
define('TESTS_SRC_DIR', realpath(__DIR__ . '/../../src'));

require_once TESTS_SRC_DIR . '/../vendor/autoload.php';

spl_autoload_register(
    function ($class)
    {
        if ('EForms' == $class)
        {
            require TESTS_SRC_DIR . '/eforms.php';
        }
        elseif (substr($class, 0, 7) == 'EForms_')
        {
            $path = TESTS_SRC_DIR . '/eforms/classes/' . str_replace('_', '/', substr($class, 7))
                . '.php';
            if (file_exists($path))
            {
                require $path;
            }
        }
    }
);

require_once 'stubs.php';

