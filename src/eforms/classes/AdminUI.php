<?php
/**
 * E-Forms
 *
 * Административный интерфейс
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
 * Административный интерфейс
 *
 * @package E-Forms
 * @since 1.01
 */
class EForms_AdminUI
{
	/**
	 * Класс модуля
	 *
	 * @var EForms
	 */
	private $plugin;

	/**
	 * Конструктор
	 *
	 * @param Plugin $plugin
	 *
	 * @return EForms_AdminUI
	 *
	 * @since 1.01
	 */
	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает разметку интерфейса
	 *
	 * @return string
	 *
	 * @since 1.01
	 */
	public function getHTML()
	{

		switch (arg('action'))
		{
			case 'add':
				$html = $this->actionAddDialog();
			break;

			case 'create':
				$html = $this->actionCreate();
			break;

			default:
				$html = $this->actionIndex();
			break;
		}
		return $html;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Главная страница
	 *
	 * @return string
	 *
	 * @since 1.01
	 */
	private function actionIndex()
	{
		$tmpl = $this->plugin->getHelper()->getAdminTemplate('list.html');
		$vars = $this->plugin->getHelper()->prepareTmplData();
		$forms = $this->plugin->getForms();
		$vars['items'] = $forms->getList();
		return $tmpl->compile($vars);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диалог добавления новой формы
	 *
	 * @return string
	 *
	 * @since 1.01
	 */
	private function actionAddDialog()
	{
		$tmpl = $this->plugin->getHelper()->getAdminTemplate('add.html');
		$vars = $this->plugin->getHelper()->prepareTmplData();
		return $tmpl->compile($vars);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сохраняет новую форму
	 *
	 * @return void
	 *
	 * @since 1.01
	 */
	private function actionCreate()
	{
		$forms = $this->plugin->getForms();
		$forms->add(arg('name'), arg('code'));
		HTTP::redirect('admin.php?mod=ext-' . $this->plugin->name);
	}
	//-----------------------------------------------------------------------------
}