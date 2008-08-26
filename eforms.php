<?php
/**
 * E-Forms
 *
 * Eresus 2
 *
 * ����������� HTML-�����
 *
 * @version 1.00a
 *
 * @copyright   2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */


/**
 * �����
 *
 */
class EForm {
	const NS = 'http://procreat.ru/eresus2/ext/eforms';
	/**
	 * Form name
	 *
	 * @var string
	 */
	private $name;
	/**
	 * Raw form code
	 *
	 * @var string
	 */
	private $code;
	/**
	 * XML representation
	 *
	 * @var DOMDocument
	 */
	private $xml;

	function __construct($name)
	{
		global $Eresus;


		$this->name = $name;

		$plugin = $Eresus->plugins->items['eforms'];
		$code = $plugin->getFormCode($name);

		if ($code) {
			$this->xml = new DOMDocument();
			$this->xml->loadXML($code);
			$this->xml->normalize();
			$this->setActionAttribute();
			$this->setActionTags();
		}
	}
	//-----------------------------------------------------------------------------
	/**
	 * Set form's action attribute
	 *
	 */
	protected function setActionAttribute()
	{
		global $Eresus;

		$form = $this->xml->getElementsByTagName('form')->item(0);
		$form->setAttribute('action', $Eresus->request['path']);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Adds hidden inputs to form
	 *
	 */
	protected function setActionTags()
	{
		$form = $this->xml->getElementsByTagName('form')->item(0);
		$div = $this->xml->createElement('div');

		$input = $this->xml->createElement('input');
		$input->setAttribute('type', 'hidden');
		$input->setAttribute('name', 'ext');
		$input->setAttribute('value', 'eforms');
		$div->appendChild($input);

		$input = $this->xml->createElement('input');
		$input->setAttribute('type', 'hidden');
		$input->setAttribute('name', 'form');
		$input->setAttribute('value', $this->name);
		$div->appendChild($input);

		$form->appendChild($div);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Return TRUE if form loaded and it is valid
	 *
	 * @return unknown
	 */
	public function valid()
	{
		return is_object($this->xml);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Get HTML form markup
	 *
	 * @return string
	 */
	public function getHTML()
	{
		$xml = clone $this->xml;
		# Clean extended tags
		$tags = $xml->getElementsByTagNameNS(self::NS, '*');
		for($i=0; $i<$tags->length; $i++) {
			$node = $tags->item($i);
			$node->parentNode->removeChild($node);
		}

		# Clean extended attrs
		$tags = $xml->getElementsByTagName('*');
		for($i=0; $i<$tags->length; $i++) {
			$node = $tags->item($i);

			$isElement = $node->nodeType == XML_ELEMENT_NODE;
			$hasAttributes = $isElement && $node->hasAttributes();

			if ($isElement && $hasAttributes) {
				$attrs = $node->attributes;
				for($j=0; $j<$attrs->length; $j++) {
					$node = $attrs->item($j);
					if ($node->namespaceURI == self::NS) $node->ownerElement->removeAttributeNode($node);
				}
			}
		}

		$xml->formatOutput = true;
		$html = $xml->saveXML();
		$html = preg_replace('/<\?.*\?>\s*/', '', $html); # Remove XML header
		$html = preg_replace('/\s*xmlns:\w+=("|\').*?("|\')/', '', $html); # Remove ns attrs

		return $html;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Return posted form data
	 *
	 * @return array
	 */
	protected function getFormData()
	{
		$data = array();
		$inputTagNames = array('input', 'textarea', 'select');

		$elements = $this->xml->getElementsByTagName('form')->item(0)->getElementsByTagName('*');

		for($i = 0; $i < $elements->length; $i++) {
			$element = $elements->item($i);

			$isElement = $element->nodeType == XML_ELEMENT_NODE;
			$isInputTag = $isElement && in_array($element->nodeName, $inputTagNames);

			if ($isInputTag) {
				switch ($element->nodeName) {
					case 'input':
						$name = $element->getAttribute('name');
						if ($name) {
							$data[$name]['data'] = arg($name);
							$label = $element->getAttributeNS(self::NS, 'label');
							if ($label) $data[$name]['label'] = iconv('utf-8', CHARSET, $label);
						}
					break;
				}
			}
		}

		return $data;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Process form actions
	 *
	 */
	public function processActions()
	{
		$actionsElement = $this->xml->getElementsByTagNameNS(self::NS, 'actions');
		if ($actionsElement) {
			$actions = $actionsElement->item(0)->childNodes;
			for($i = 0; $i < $actions->length; $i++) {
				$action = $actions->item($i);
				if ($action->nodeType == XML_ELEMENT_NODE) $this->processAction($action);
			}
		}
		die;
	}
	//-----------------------------------------------------------------------------
	/**
	 * Process action directive
	 *
	 * @param DOMElement $action
	 */
	protected function processAction($action)
	{
		$actionName = substr($action->nodeName, strlen($action->lookupPrefix(self::NS))+1);
		$methodName = 'action'.$actionName;
		if (method_exists($this, $methodName)) $this->$methodName($action);
	}
	//-----------------------------------------------------------------------------
	/**
	 * Process 'mailto' action
	 *
	 * @param DOMElement $action
	 */
	protected function actionMailto($action)
	{
		$to = $action->getAttribute('to');
		$subj = $action->getAttribute('subj');
		$from = $action->getAttribute('from');
		$data = $this->getFormData();
		var_dump($data);
	}
	//-----------------------------------------------------------------------------
}

class EForms extends Plugin {
	var $version = '1.00a';
	var $kernel = '2.10';
	var $title = 'E-Forms';
	var $description = '����������� HTML-�����';
	var $type = 'client,admin';

	/**
	 * ������ ��������� ����
	 *
	 * @var array
	 *
	 * @access private
	 */
	var $forms = null;
	/**
	 * ��������� ������� Templates
	 *
	 * @var object Templates
	 *
	 * @access private
	 */
	var $templates = null;


	/**
	 * �����������
	 *
	 * @return EForms
	 */
	function EForms()
	{
		parent::Plugin();
		$this->listenEvents('clientOnStart', 'clientOnPageRender');
	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� ��� ��������� �������
	 *
	 */
	function install()
	{
		global $Eresus;

		parent::install();

		$umask = umask(0000);
		mkdir($Eresus->froot.'templates/'.$this->name, 0777);
		umask($umask);

		#TODO: �������� ���������� � ���� ��� �������������

	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� ������ Templates
	 *
	 * @return object Templates
	 */
	function getTemplates()
	{
		if (is_null($this->templates)) {
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
	function getForms()
	{
		if (is_null($this->forms)) {
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
	function getFormCode($name)
	{
		$templates = $this->getTemplates();
		$form = $templates->get($name, $this->name);

		return $form;
	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� ������ ����� ������
	 *
	 * @param string $action
	 * @param string $form
	 * @deprecated
	 */
	function actionMailto($action, $form)
	{
		$action = explode(';', $action);
		$mail = trim(substr($action[0], 7));
		$subj = $this->name;
		for($i=1; $i < count($action); $i++) {
			list($key, $value) = explode('=', $action[$i]);
			switch ($key) {
				case 'subject': $subj = $value; break;
			}
		}
		$elements = $this->parseForm($form);
		$text = '';
		foreach($elements as $element) {
			switch ($element->nodeName) {
				case 'input':
					switch($element->getAttribute('type')) {
						case 'text':
							$label = iconv('utf-8', 'windows-1251', $element->getAttribute('label'));
							if (!$label) $label = $element->getAttribute('name');
							$text .= $label.': '.arg($element->getAttribute('name'))."\n";
						break;
					}
				break;
				case 'textarea':
				break;
			}
			$text .= "\n";
		}
		sendMail($mail, $subj, $text);
	}
	//-----------------------------------------------------------------------------
	/**
	 * ��������� �������� �����
	 *
	 * @param string $action
	 * @param string $form
	 * @deprecated
	 */
	function processAction($action, $form)
	{
		switch (true) {
			case substr($action, 0, 7) == 'mailto:': $this->actionMailto($action, $form);
		}
	}
	//-----------------------------------------------------------------------------
	/**
	 * ����������� ���� �� ��������
	 *
	 * @param string $text
	 * @return string
	 */
	function clientOnPageRender($text)
	{

		$text = preg_replace_callback('/\$\('.$this->name.':(.*)\)/Usi', array($this, 'buildForm'), $text);
		return $text;
	}
	//-----------------------------------------------------------------------------
	/**
	 * HTML-��� �����
	 *
	 * @param array $macros
	 * @return string
	 */
	function buildForm($macros)
	{
		$result = $macros[0];

		$form = new EForm($macros[1]);

		if ($form->valid()) $result = $form->getHTML();

		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * ��������� ������������ ����
	 *
	 */
	function clientOnStart()
	{
		global $Eresus;

		if (arg('ext') == $this->name) {
			$form = new EForm(arg('form', 'word'));
			$form->processActions();
			goto($Eresus->request['referer']);
		}
	}
	//-----------------------------------------------------------------------------
}
