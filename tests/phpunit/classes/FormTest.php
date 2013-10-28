<?php
/**
 * Тесты класса EForms_Form
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

require_once __DIR__ . '/../bootstrap.php';

class EForms_FormTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers EForms_Form::processActions
     */
    public function testMarkerRedirect()
    {
        $forms = $this->getMock('stdClass', array('getFormCode'));
        $forms->expects($this->once())->method('getFormCode')->will($this->returnValue(
            '<form xmlns:ef="http://procreat.ru/eresus2/ext/eforms">' .
            '<ef:actions>' .
                '<ef:redirect uri="/baz/" />' .
                '<ef:marker value="foo" />' .
            '</ef:actions>' .
            '</form>'
        ));

        $plugin = $this->getMock('EForms', array('getForms'));
        $plugin->expects($this->any())->method('getForms')->will($this->returnValue($forms));

        $legacyKernel = new stdClass();
        $legacyKernel->froot = __DIR__ . '/FormTest.fixtures/';
        $legacyKernel->request = array(
            'path' => 'http://example.org/foo/',
            'referer' => 'http://example.org/bar/',
        );

        $page = $this->getMock('stdClass', array('replaceMacros'));
        $page->expects($this->any())->method('replaceMacros')->will($this->returnArgument(0));

        $cms = $this->getMock('stdClass', array('getLegacyKernel', 'getPage'));
        $cms->expects($this->any())->method('getLegacyKernel')
            ->will($this->returnValue($legacyKernel));
        $cms->expects($this->any())->method('getPage')->will($this->returnValue($page));
        Eresus_CMS::setMock($cms);

        $kernel = $this->getMock('stdClass', array('app'));
        $kernel->expects($this->any())->method('app')->will($this->returnValue($cms));
        Eresus_Kernel::setMock($kernel);

        $http = $this->getMock('stdClass', array('redirect'));
        $http->expects($this->once())->method('redirect')->with('/baz/#foo');
        HTTP::setMock($http);

        /** @var EForms $plugin */
        $form = new EForms_Form($plugin, 'foo');
        $form->processActions();
    }

    /**
     * @covers EForms_Form::setActionAttribute
     */
    public function testMarkerNoRedirect()
    {
        $setActionAttribute = new ReflectionMethod('EForms_Form', 'setActionAttribute');
        $setActionAttribute->setAccessible(true);

        $xml = new ReflectionProperty('EForms_Form', 'xml');
        $xml->setAccessible(true);

        $plugin = $this->getMock('EForms', array('none'));

        /** @var EForms $plugin */
        $form = new EForms_Form($plugin, 'foo');

        $imp = new DOMImplementation;
        $dtd = $imp->createDocumentType('html', '-//W3C//DTD XHTML 1.0 Strict//EN',
            'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd');
        $doc = $imp->createDocument("", "", $dtd);
        $doc->loadXML(
            '<form xmlns:ef="http://procreat.ru/eresus2/ext/eforms">' .
            '<ef:actions>' .
            '<ef:marker value="foo" />' .
            '</ef:actions>' .
            '</form>'
        );
        $doc->encoding = 'utf-8';
        $doc->normalize();
        $xml->setValue($form, $doc);

        $legacyKernel = new stdClass();
        $legacyKernel->froot = __DIR__ . '/FormTest.fixtures/';
        $legacyKernel->request = array(
            'path' => 'http://example.org/foo/',
        );

        $cms = $this->getMock('stdClass', array('getLegacyKernel'));
        $cms->expects($this->any())->method('getLegacyKernel')
            ->will($this->returnValue($legacyKernel));
        Eresus_CMS::setMock($cms);

        $setActionAttribute->invoke($form);

        $this->assertEquals('http://example.org/foo/#foo',
            $doc->getElementsByTagName('form')->item(0)->getAttribute('action'));
    }
}

