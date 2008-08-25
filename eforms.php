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
	 * �������� ������ �����
	 *
	 * @param string $name
	 * @return DOMDocument
	 */
	function getForm($name)
	{
		$html = $this->getFormCode($name);

		$form = new DOMDocument();
		$form->loadXML($html);
		$form->normalize();
		$x = $form->getElementsByTagName('input');
		$x = $x->item(0);
		$x = $x->attributes;
		$x = $x->item(2);
		var_dump($x->nodeName);
		die();

		return $form;
	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� �������������� � �����
	 *
	 * @param unknown_type $form
	 * @return unknown
	 */
	function getMetaInfo($form)
	{
		if ($info = preg_match_all('/<meta.*>/Usi', $form, $meta)) {
			$info = array();
			for($i = 0; $i < count($meta[0]); $i++) {
				preg_match('/name="(.*)"/Ui', $meta[0][$i], $name);
				preg_match('/content="(.*)"/Ui', $meta[0][$i], $content);
				$info[$name[1]][] = $content[1];
			}
		}

		return $info;
	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� �������������� �� ���� �����
	 *
	 * @param string $html
	 * @return string
	 */
	function stripMetaInfo($html)
	{
		$html = preg_replace('/<meta.*>\s*?/Usi', '', $html);
		$html = preg_replace('/\s*'.$this->name.':\w+=".*"/Usi', '', $html);
		return $html;
	}
	//-----------------------------------------------------------------------------
	/**
	 * ������������� �������� �������� action
	 *
	 * @param string $html
	 * @param string $formName
	 * @return string
	 */
	function setActionAttr($html, $formName)
	{
		global $Eresus;

		$html = preg_replace('/(<form[^>]*)\s+action=(")?(\')?.*?(?(2)"|\')/si', '$1', $html);
		$html = preg_replace('/(<form)/i', '$1 action="'.$Eresus->request['path'].'"', $html);
		$html = preg_replace('/(<form[^>]*>\s*?)/Usi',
			'$1<div class="hidden">'.
			'<input type="hidden" name="ext" value="'.$this->name.'" />'.
			'<input type="hidden" name="form" value="'.$formName.'" /></div>',
			$html
		);
		return $html;
	}
	//-----------------------------------------------------------------------------
	/**
	 * ��������� HTML-���� �����
	 *
	 * @param string $form
	 * @param string $formName
	 * @return string
	 */
	function renderForm($form, $formName)
	{
		$result = $form;

		$result = $this->setActionAttr($result, $formName);

		$result = $this->stripMetaInfo($result);

		return $result;
	}
	//-----------------------------------------------------------------------------
	/**
	 * ������ �����
	 *
	 * @param string $form
	 * @return DOMDocument
	 */
	function parseForm($form)
	{
		$xml = new DOMDocument();
		$xml->loadXML('<?xml version="1.0" encoding="windows-1251"?>'."\n<eforms>".$form.'</eforms>');
		$form = $xml->getElementsByTagName('form');
		$form = $form->item(0);
		$children = $form->getElementsByTagName('*');
		$elements = array();
		$i = 0;
		while ($node = $children->item($i++)) if (in_array($node->nodeName, array('input','textarea','select'))) $elements[]=$node;

		return $elements;
	}
	//-----------------------------------------------------------------------------
	/**
	 * �������� ������ ����� ������
	 *
	 * @param string $action
	 * @param string $form
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

		$name = $macros[1];
		$form = $this->getForm($name);

		die;

		if ($form) {


			$form = $this->getForm($name);

			$templates = $this->getTemplates();
			$form = $templates->get($form_name, $this->name);
			$result = $this->renderForm($form, $form_name);
		}
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
			$form = $this->getFormCode(arg('form'));
			$meta = $this->getMetaInfo($form);
			#$form = $this->stripMetaInfo($form);
			foreach ($meta['action'] as $action) $this->processAction($action, $form);
			goto($Eresus->request['referer']);
		}
	}
	//-----------------------------------------------------------------------------
}
