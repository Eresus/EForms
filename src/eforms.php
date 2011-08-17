<?php
/**
 * E-Forms
 *
 * Расширенные HTML-формы
 *
 * @version 1.00
 *
 * @copyright 2008, Eresus Group, http://eresus.ru/
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Mikhail Krasilnikov <mk@procreat.ru>
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
 * Класс плагина
 *
 * @package E-Forms
 */
class EForms extends Plugin
{
	/**
	 * Версия плагина
	 * @var string
	 */
	public $version = '1.01a';

	/**
	 * Требуемая версия ядра
	 * @var string
	 */
	public $kernel = '2.12b';

	/**
	 * Название плагина
	 * @var string
	 */
	public $title = 'E-Forms';

	/**
	 * Описание плагина
	 * @var string
	 */
	public $description = 'Расширенные HTML-формы';

	/**
	 * Тип плагина
	 * @var string
	 */
	public $type = 'client,admin';

	/**
	 * Список доступных форм
	 *
	 * @var array
	 */
	private $forms = null;

	/**
	 * Экземпляр объекта Templates
	 *
	 * @var object Templates
	 */
	private $templates = null;

	/**
	 * Констурктор
	 *
	 * @return EForms
	 */
	public function __construct()
	{
		parent::__construct();
		$this->listenEvents('clientOnContentRender', 'clientOnPageRender');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Действия при установке плагина
	 *
	 */
	public function install()
	{
		global $Eresus;

		parent::install();

		if (!is_dir($Eresus->froot.'templates/'.$this->name))
		{
			$umask = umask(0000);
			mkdir($Eresus->froot.'templates/'.$this->name, 0777);
			umask($umask);
		}

		#TODO: Добавить удаление директории и форм при деинсталляции

	}
	//-----------------------------------------------------------------------------

	/**
	 * Получить объект Templates
	 *
	 * @return object Templates
	 */
	public function getTemplates()
	{
		if (is_null($this->templates))
		{
			useLib('templates');
			$this->templates = new Templates();
		}

		return $this->templates;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Получить список доступных форм
	 *
	 * @return array
	 */
	public function getForms()
	{
		if (is_null($this->forms))
		{
			$templates = $this->getTemplates();
			$this->forms = $templates->enum($this->name);
		}

		return $this->forms;
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
	 * Подстановка форм на страницу
	 *
	 * @param string $text
	 * @return string
	 */
	public function clientOnPageRender($text)
	{
		$text = preg_replace_callback('/\$\('.$this->name.':(.*)\)/Usi', array($this, 'buildForm'),
			$text);
		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * HTML-код формы
	 *
	 * @param array $macros
	 * @return string
	 */
	public function buildForm($macros)
	{
		$result = $macros[0];

		$form = new EForm($this, $macros[1]);

		if ($form->valid())
		{
			$result = $form->getHTML();
		}

		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обработка отправленных форм
	 *
	 */
	public function clientOnContentRender($content)
	{
		if (arg('ext') == $this->name)
		{
			$form = new EForm($this, arg('form', 'word'));
			$content = $form->processActions();
		}
		return $content;
	}
	//-----------------------------------------------------------------------------
}