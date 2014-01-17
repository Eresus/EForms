<?php
/**
 * E-Forms
 *
 * Расширенные HTML-формы
 *
 * @version ${product.version}
 *
 * @copyright 2008, Eresus Group, http://eresus.ru/
 * @copyright 2010, ООО "Два слона", http://dvaslona.ru/
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
	public $version = '${product.version}';

	/**
	 * Требуемая версия ядра
	 * @var string
	 */
	public $kernel = '3.00';

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
	 * Объект-помощник
	 *
	 * @var EForms_Helper
	 * @since 1.01
	 */
	private $helper;

	/**
	 * Интерфейс к формам
	 *
	 * @var EForms_Forms
	 * @since 1.01
	 */
	private $forms;

	/**
	 * Конструктор
	 *
	 * @return EForms
	 */
	public function __construct()
	{
		parent::__construct();
		$this->listenEvents('clientOnContentRender', 'clientOnPageRender', 'adminOnMenuRender');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Действия при установке плагина
	 *
	 */
	public function install()
	{
		parent::install();

		$Eresus = Eresus_CMS::getLegacyKernel();
		if (!is_dir($Eresus->froot.'templates/'.$this->name))
		{
			$umask = umask(0000);
			mkdir($Eresus->froot.'templates/'.$this->name, 0777);
			umask($umask);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see Plugin::uninstall()
	 */
	public function uninstall()
	{
		$Eresus = Eresus_CMS::getLegacyKernel();
		$tmplDir = $Eresus->froot . 'templates/' . $this->name;
		if (is_dir($tmplDir))
		{
			$it = new RecursiveDirectoryIterator($tmplDir, RecursiveDirectoryIterator::SKIP_DOTS);
			foreach ($it as $file)
			{
				@unlink($file);
			}
		}
		@rmdir($tmplDir);

		parent::uninstall();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объект-помощник
	 *
	 * @return EForms_Helper
	 *
	 * @since 1.01
	 */
	public function getHelper()
	{
		if (!$this->helper)
		{
			$this->helper = new EForms_Helper($this);
		}
		return $this->helper;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает интерфейс к формам
	 *
	 * @return EForms_Forms
	 *
	 * @since 1.01
	 */
	public function getForms()
	{
		if (!$this->forms)
		{
			$this->forms = new EForms_Forms($this);
		}
		return $this->forms;
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
		try
		{
			$form = new EForms_Form($this, $macros[1]);
		}
		catch (Exception $e)
		{
			return $macros[0];
		}

		return $form->getHTML();
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
			$form = new EForms_Form($this, arg('form', 'word'));
			$content = $form->processActions();
		}
		return $content;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Интерфейс управления формами
	 *
	 * @return string  HTML
	 *
	 * @since 1.00
	 */
	public function adminRender()
	{
		$ui = new EForms_AdminUI($this);
		return $ui->getHTML();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет пункт "Формы ввода" в меню "Расширения"
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function adminOnMenuRender()
	{
		/** @var TAdminUI $page */
		$page = Eresus_Kernel::app()->getPage();
		$page->addMenuItem(admExtensions, array(
			'access'  => EDITOR,
			'link'  => $this->name,
			'caption'  => 'Формы ввода',
			'hint'  => $this->description
		));
	}
	//-----------------------------------------------------------------------------
}

