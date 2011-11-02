<?php
/**
 * E-Forms
 *
 * ����������� HTML-�����
 *
 * @version ${product.version}
 *
 * @copyright 2008, Eresus Group, http://eresus.ru/
 * @copyright 2010, ��� "��� �����", http://dvaslona.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author ������ ������������ <mihalych@vsepofigu.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� (�� ������ ������) � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 *
 * @package E-Forms
 *
 * $Id$
 */


/**
 * ����� �������
 *
 * @package E-Forms
 */
class EForms extends Plugin
{
	/**
	 * ������ �������
	 * @var string
	 */
	public $version = '${product.version}';

	/**
	 * ��������� ������ ����
	 * @var string
	 */
	public $kernel = '2.14';

	/**
	 * �������� �������
	 * @var string
	 */
	public $title = 'E-Forms';

	/**
	 * �������� �������
	 * @var string
	 */
	public $description = '����������� HTML-�����';

	/**
	 * ��� �������
	 * @var string
	 */
	public $type = 'client,admin';

	/**
	 * ������-��������
	 *
	 * @var EForms_Helper
	 * @since 1.01
	 */
	private $helper;

	/**
	 * ��������� � ������
	 *
	 * @var EForms_Forms
	 * @since 1.01
	 */
	private $forms;

	/**
	 * �����������
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
	 * �������� ��� ��������� �������
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
	}
	//-----------------------------------------------------------------------------

	/**
	 * @see Plugin::uninstall()
	 */
	public function uninstall()
	{
		$tmplDir = $GLOBALS['Eresus']->froot . 'templates/' . $this->name;
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
	 * ���������� ������-�������
	 *
	 * @return EForms_Helper
	 *
	 * @since 1.01
	 */
	public function getHelper()
	{
		if (!$this->helper)
		{
			$this->verifyClassLoaded('EForms_Helper');
			$this->helper = new EForms_Helper($this);
		}
		return $this->helper;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ��������� � ������
	 *
	 * @return EForms_Forms
	 *
	 * @since 1.01
	 */
	public function getForms()
	{
		if (!$this->forms)
		{
			$this->verifyClassLoaded('EForms_Forms');
			$this->forms = new EForms_Forms($this);
		}
		return $this->forms;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ����������� ���� �� ��������
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
	 * HTML-��� �����
	 *
	 * @param array $macros
	 * @return string
	 */
	public function buildForm($macros)
	{
		$this->verifyClassLoaded('EForms_Form');

		try
		{
			$form = new EForms_Form($this, $macros[1]);
		}
		catch (Exception $e)
		{
			return $macros[0];
			$e = $e; // PHPMD hack
		}

		return $form->getHTML();
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ������������ ����
	 *
	 */
	public function clientOnContentRender($content)
	{
		$this->verifyClassLoaded('EForms_Form');
		if (arg('ext') == $this->name)
		{
			$form = new EForms_Form($this, arg('form', 'word'));
			$content = $form->processActions();
		}
		return $content;
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ���������� �������
	 *
	 * @return string  HTML
	 *
	 * @since 1.00
	 */
	public function adminRender()
	{
		$this->verifyClassLoaded('EForms_AdminUI');
		$ui = new EForms_AdminUI($this);
		return $ui->getHTML();
	}
	//-----------------------------------------------------------------------------

	/**
	 * ��������� ����� "����� �����" � ���� "����������"
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function adminOnMenuRender()
	{
		$GLOBALS['page']->addMenuItem(admExtensions, array(
			'access'  => EDITOR,
			'link'  => $this->name,
			'caption'  => '����� �����',
			'hint'  => $this->description
		));
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ��� ������ ����� ��������
	 *
	 * @param string $className
	 *
	 * @return void
	 *
	 * @since 1.01
	 */
	public function verifyClassLoaded($className)
	{
		if (!class_exists($className))
		{
			require_once dirname(__FILE__) . '/' . $this->name . '/classes/' .
				substr($className, strlen('EForms_')) . '.php';
		}
	}
	//-----------------------------------------------------------------------------
}