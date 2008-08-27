<?php
/**
 * E-Forms
 *
 * Eresus 2
 *
 * Расширенные HTML-формы
 *
 * @version 1.00b
 *
 * @copyright   2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */


class EForms extends Plugin {
	var $version = '1.00b';
	var $kernel = '2.10';
	var $title = 'E-Forms';
	var $description = 'Расширенные HTML-формы';
	var $type = 'client,admin';

	/**
	 * Список доступных форм
	 *
	 * @var array
	 *
	 * @access private
	 */
	var $forms = null;
	/**
	 * Экземпляр объекта Templates
	 *
	 * @var object Templates
	 *
	 * @access private
	 */
	var $templates = null;


	/**
	 * Констурктор
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
	 * Действия при установке плагина
	 *
	 */
	function install()
	{
		global $Eresus;

		parent::install();

		$umask = umask(0000);
		mkdir($Eresus->froot.'templates/'.$this->name, 0777);
		umask($umask);

		#TODO: Удаление директории и форм при деинсталляции

	}
	//-----------------------------------------------------------------------------
	/**
	 * Получить объект Templates
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
	 * Получить список доступных форм
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
	 * Получить код формы
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
	 * Подстановка форм на страницу
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
	 * HTML-код формы
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
	 * Обработка отправленных форм
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

/**
 * Форма
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
		#$from = $action->getAttribute('from');
		$data = $this->getFormData();

		if (!$to) return false;
		if (!$subj) $subj = $this->name;
		#$from = $action->getAttribute('from');

		$text = '';
		foreach ($data as $item) {
			if (!isset($item['label'])) continue;
			$text .= $item['label'].': '.$item['data']."\n";
		}

		sendMail($to, $subj, $text);
	}
	//-----------------------------------------------------------------------------
}
