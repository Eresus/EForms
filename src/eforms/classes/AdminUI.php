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
		$html = '';
		switch (arg('action'))
		{
			case 'add':
				$html = $this->actionAddDialog();
			break;

			case 'create':
				$html = $this->actionCreate();
			break;

			default:

				switch (true)
				{
					case arg('edit'):
						$html = $this->actionEditDialog(arg('edit'));
					break;

					case arg('update'):
						$html = $this->actionUpdate();
					break;

					case arg('delete'):
						$this->actionDelete(arg('delete'));
					break;

					default:
						$html = $this->actionIndex();
				}
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
		$vars['rootURL'] = $GLOBALS['page']->url();
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
		$form = array(
			'name' => 'add',
			'caption' => 'Добавление формы',
			'width' => '100%',
			'fields' => array (
				array('type' => 'hidden','name' => 'action', 'value' => 'create'),
				array('type' => 'edit', 'name'=>'title', 'label' => 'Описание',
					'width' => '99%', 'value' => arg('title')),
				array('type' => 'edit', 'name' => 'name', 'label' => 'Имя',
					'width'=>'16em', 'value' => arg('name'), 'comment' => 
					'только латинские буквы, цифры, символы минус и подчёркивание',
					'pattern' => '/^[\w\-]+$/i',
					'errormsg' => 
					'Имя формы может только латинские буквы, цифры, символы минус и подчёркивание'),
				array('type' => 'text',
					'value' =>
						'&raquo; <b><a href="http://docs.eresus.ru/cms-plugins/eforms/usage/language">' .
						'Синтаксис форм</a></b>'),
				array('type' => 'memo', 'name' => 'code', 'height' => '30', 'syntax' => 'html',
					'value' => arg('code') ? arg('code') :
					'<form xmlns:ef="http://procreat.ru/eresus2/ext/eforms" method="post">' .	"\n</form>"),
			),
			'buttons' => array('ok','cancel'),
		);
		return $GLOBALS['page']->renderForm($form);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Диалог изменения формы
	 *
	 * @param string $name
	 *
	 * @return string
	 *
	 * @since 1.01
	 */
	private function actionEditDialog($name)
	{
		$forms = $this->plugin->getForms();
		$item = $forms->get($name);

		$form = array(
			'name' => 'edit',
			'caption' => $item['title'],
			'width' => '100%',
			'fields' => array (
				array('type' => 'hidden','name' => 'update', 'value' => $item['name']),
				array('type' => 'edit', 'name'=>'title', 'label' => 'Описание',
					'width' => '99%', 'value' => arg('title') ? arg('title') : $item['title']),
				array('type' => 'edit', 'name' => 'name', 'label' => 'Имя',
					'width'=>'16em', 'comment' =>
					'только латинские буквы, цифры, символы минус и подчёркивание',
					'value' => arg('name') ? arg('name') : $item['name'],
					'pattern' => '/^[\w\-]+$/i',
					'errormsg' => 
					'Имя формы может только латинские буквы, цифры, символы минус и подчёркивание'),
				array('type' => 'text',
					'value' =>
						'&raquo; <b><a href="http://docs.eresus.ru/cms-plugins/eforms/usage/language">' .
						'Синтаксис форм</a></b>'),
				array('type' => 'memo', 'name' => 'code', 'height' => '30', 'syntax' => 'html',
					'value' => arg('code') ? arg('code') : $item['code']),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		return $GLOBALS['page']->renderForm($form);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Сохраняет новую форму
	 *
	 * @return string
	 *
	 * @since 1.01
	 */
	private function actionCreate()
	{
		$forms = $this->plugin->getForms();
		$name = arg('name');
		if ($forms->get($name) === false)
		{
			$forms->add($name, arg('code'), arg('title'));
			HTTP::redirect(arg('submitURL'));
		}
		ErrorMessage('Форма с таким именем уже есть! Укажите другое имя.');
		return $this->actionAddDialog();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обновляет форму
	 *
	 * @return string
	 *
	 * @since 1.01
	 */
	private function actionUpdate()
	{
		$oldName = arg('update');
		$newName = arg('name');
		$forms = $this->plugin->getForms();
		if ($oldName == $newName || $forms->get($newName) === false)
		{
			$forms->update($oldName, arg('code'), arg('title'));

			$url = arg('submitURL');
			if ($oldName != $newName)
			{
				$forms->rename($oldName, $newName);
				$url = str_replace('edit=' . $oldName, 'edit=' . $newName, $url);
			}
			HTTP::redirect($url);
		}
		ErrorMessage('Форма с таким именем уже есть! Укажите другое имя.');
		return $this->actionEditDialog($oldName);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет форму
	 *
	 * @param string $name
	 *
	 * @return void
	 *
	 * @since 1.01
	 */
	private function actionDelete($name)
	{
		$forms = $this->plugin->getForms();
		$forms->delete($name);
		HTTP::redirect($GLOBALS['page']->url());
	}
	//-----------------------------------------------------------------------------
}