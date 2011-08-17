<?php
/**
 * E-Forms
 *
 * ����������� HTML-�����
 *
 * @version 1.01
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
	public $version = '1.01a';

	/**
	 * ��������� ������ ����
	 * @var string
	 */
	public $kernel = '2.12b';

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
	 * ������ ��������� ����
	 *
	 * @var array
	 */
	private $forms = null;

	/**
	 * ��������� ������� Templates
	 *
	 * @var object Templates
	 */
	private $templates = null;

	/**
	 * �����������
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
	 * �������� ������ Templates
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
	 * �������� ������ ��������� ����
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
	 * �������� ��� �����
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
	 * ��������� ������������ ����
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