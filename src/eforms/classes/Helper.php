<?php
/**
 * E-Forms
 *
 * Класс-помощник
 *
 * @version 1.01
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
*
* $Id$
*/


/**
 * Класс-помощник
 *
 * Класс содержит вспомогательный функционал
 *
 * @package E-Forms
 */
class EForms_Helper
{
	/**
	 * Объект плагина
	 *
	 * @var EForms
	 */
	private $plugin;

	/**
	 * Конструктор
	 *
	 * @param EForms $plugin
	 *
	 * @return EForms_Helper
	 */
	public function __construct(EForms $plugin)
	{
		$this->plugin = $plugin;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает экземпляр шаблона АИ с указанным именем
	 *
	 * @param string $name  Имя файла шаблона относительно директории шаблонов плагина
	 *
	 * @return Template
	 *
	 * @since 1.00
	 */
	public function getAdminTemplate($name)
	{
		$tmpl = new Template('ext/' . $this->plugin->name . '/templates/' . $name);
		return $tmpl;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает массив данных для шаблона.
	 *
	 * Массив предварительно наполняется часто используемыми переменными.
	 *
	 * @return array
	 *
	 * @since 1.00
	 */
	public function prepareTmplData()
	{
		$data = array();
		$data['plugin'] = $this->plugin;
		$data['page'] = Eresus_Kernel::app()->getPage();
		$data['Eresus'] = Eresus_CMS::getLegacyKernel();
		return $data;
	}
	//-----------------------------------------------------------------------------
}
