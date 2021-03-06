<?php
/**
 * Тесты класса EForms
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

require_once 'bootstrap.php';

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

class EFormsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers EForms::uninstall
     */
    public function testUninstall()
    {
        $plugin = $this->getMockBuilder('EForms')->disableOriginalConstructor()->
            setMethods(array('none'))->getMock();
        /** @var Plugin $plugin */
        $plugin->name = 'eforms';

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('htdocs'));
        $d_templates = new vfsStreamDirectory('templates');
        vfsStreamWrapper::getRoot()->addChild($d_templates);
        $d_eforms = new vfsStreamDirectory('eforms');
        $d_templates->addChild($d_eforms);
        for ($i = 1; $i < 5; $i++)
        {
            $file = new vfsStreamFile('form' . $i . '.html');
            $d_eforms->addChild($file);
        }

        $legacyKernel = new stdClass();
        $legacyKernel->froot = vfsStream::url('htdocs') . '/';
        $cms = $this->getMock('stdClass', array('getLegacyKernel'));
        $cms->expects($this->any())->method('getLegacyKernel')
            ->will($this->returnValue($legacyKernel));
        Eresus_CMS::setMock($cms);

        $plugin->uninstall();

        $this->assertFalse($d_templates->hasChild('eforms'));
    }
}

