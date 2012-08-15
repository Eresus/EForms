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
	 */
	public function __construct(EForms $plugin)
	{
		$this->plugin = $plugin;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет новую форму
	 *
	 * @param string $name   имя файла (без пути и расширения)
	 * @param string $code   код формы
	 * @param string $title  название формы
	 *
	 * @return void
	 *
	 * @since 1.01
	 */
	public function add($name, $code, $title)
	{
		$templates = $this->getTemplates();
		$templates->add($name, $this->plugin->name, $code, $title);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Изменяет форму
	 *
	 * @param string $name   имя файла (без пути и расширения)
	 * @param string $code   код формы
	 * @param string $title  название формы
	 *
	 * @return void
	 *
	 * @since 1.01
	 */
	public function update($name, $code, $title)
	{
		$templates = $this->getTemplates();
		$templates->update($name, $this->plugin->name, $code, $title);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Переименовывает форму
	 *
	 * @param string $oldName   старое имя файла (без пути и расширения)
	 * @param string $newName   новое имя файла (без пути и расширения)
	 *
	 * @return void
	 *
	 * @since 1.01
	 */
	public function rename($oldName, $newName)
	{
		$form = $this->get($oldName);
		$this->delete($oldName);
		$this->add($newName, $form['code'], $form['title']);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет форму
	 *
	 * @param string $name  имя файла (без пути и расширения)
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function delete($name)
	{
		$templates = $this->getTemplates();
		$templates->delete($name, $this->plugin->name);
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
	 * Возвращает форму
	 *
	 * @param string $name
	 *
	 * @return array
	 *
	 * @since 1.01
	 */
	public function get($name)
	{
		$templates = $this->getTemplates();
		$form = $templates->get($name, $this->plugin->name, true);
		$form['title'] = $form['desc'];
		unset($form['desc']);
		return $form;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Получить код формы
	 *
	 * @param string $name
	 *
	 * @return string
	 *
	 * @since 1.01
	 */
	public function getFormCode($name)
	{
		$templates = $this->getTemplates();
		return $templates->get($name, $this->plugin->name);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объект Templates
	 *
	 * @return Templates
	 *
	 * @since 1.01
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
