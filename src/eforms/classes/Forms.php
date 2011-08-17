<?php
/**
 * E-Forms
 *
 * Набор форм
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
 * Набор форм
 *
 * @package E-Forms
 * @since 1.01
 */
class EForms_Forms
{
	/**
	 * Объект плагина
	 *
	 * @var EForms
	 */
	private $plugin;

	/**
	 * Экземпляр объекта Templates
	 *
	 * @var object Templates
	 */
	private $templates = null;

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
	 * Добавляет новую форму
	 *
	 * @param string $name  имя файла (без пути и расширения)
	 * @param string $code  код формы
	 *
	 * @return void
	 *
	 * @since 1.01
	 */
	public function add($name, $code)
	{
		$templates = $this->getTemplates();
		$templates->add($name, $this->plugin->name, $code);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает список форм
	 *
	 * @return array
	 *
	 * @since 1.01
	 */
	public function getList()
	{
		$templates = $this->getTemplates();
		return $templates->enum($this->plugin->name);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Получить код формы
	 *
	 * @param string $name
	 * @return string
	 */
	public function getFormCode($name)
	{

		$templates = $this->getTemplates();
		$form = $templates ? $templates->get($name, $this->name) : false;

		return $form;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объект Templates
	 *
	 * @return Templates
	 */
	private function getTemplates()
	{
		if (is_null($this->templates))
		{
			useLib('templates');
			$this->templates = new Templates();
		}

		return $this->templates;
	}
	//-----------------------------------------------------------------------------
}
