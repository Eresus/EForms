<?php
/**
 * Форма
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
 * Форма
 *
 * @package E-Forms
 */
class EForms_Form
{
    /**
     * Пространство имён XML
     *
     * @todo постепенно заменить на «http://eresus.ru/specs/cms/plugins/eforms»
     */
    const NS = 'http://procreat.ru/eresus2/ext/eforms';

    /**
     * Плагин-владелец
     *
     * @var EForms
     */
    protected $owner;

    /**
     * Имя формы
     *
     * @var string
     */
    protected $name;

    /**
     * Сырой код
     *
     * @var string
     */
    protected $code;

    /**
     * XML-представление
     *
     * @var DOMDocument
     */
    protected $xml;

    /**
     * URL для перенаправления
     *
     * @var mixed
     */
    protected $redirect = false;

    /**
     * Содержимое тега <html>
     *
     * @var string
     */
    protected $html = '';

    /**
     * Конструктор
     *
     * @param EForms $owner  Плагин-владелец
     * @param string $name   Имя формы
     *
     * @return EForms_Form
     */
    public function __construct(EForms $owner, $name)
    {
        $this->owner = $owner;
        $this->name = $name;
    }

    //-----------------------------------------------------------------------------

    /**
     * Возвращает HTML-разметку формы
     *
     * @return string
     */
    public function getHTML()
    {
        $this->parse();
        $xml = clone $this->xml;

        /* Удаляем расширенные теги */
        $tags = $xml->getElementsByTagNameNS(self::NS, '*');
        while ($tags->length > 0)
        {
            $node = $tags->item(0);
            $node->parentNode->removeChild($node);
        }

        // Есть ли поля для выбора файлов?
        $hasFileInputs = false;

        /*
         * Удаляем расширенные атрибуты
         * Ищем input[type=file]
         */
        $tags = $xml->getElementsByTagName('*');
        for ($i = 0; $i < $tags->length; $i++)
        {
            $node = $tags->item($i);

            $isElement = $node->nodeType == XML_ELEMENT_NODE;
            /** @var DOMElement $node */
            $hasAttributes = $isElement && $node->hasAttributes();

            if ($isElement && $hasAttributes)
            {
                /** @var DOMNamedNodeMap $attrs */
                $attrs = $node->attributes;
                for ($j = 0; $j < $attrs->length; $j++)
                {
                    /** @var DOMAttr $attr */
                    $attr = $attrs->item($j);
                    if ($attr->namespaceURI == self::NS)
                    {
                        $attr->ownerElement->removeAttributeNode($attr);
                        $j--;
                    }
                }

                if ($node->tagName == 'input' && $node->getAttribute('type') == 'file')
                {
                    $hasFileInputs = true;
                }
            }
        }

        if ($hasFileInputs)
        {
            /** @var DOMElement $element */
            $element = $xml->firstChild->nextSibling;
            $element->setAttribute('enctype', 'multipart/form-data');
        }

        // Предотвращаем схлопывание пустых textarea
        $tags = $xml->getElementsByTagName('textarea');
        for ($i = 0; $i < $tags->length; $i++)
        {
            $node = $tags->item($i);
            $cdata = $xml->createCDATASection('');
            $node->appendChild($cdata);
        }

        $xml->formatOutput = true;
        $html = $xml->saveXML($xml->firstChild->nextSibling);
        // Удаляем атрибуты пространств имён
        $html = preg_replace('/\s*xmlns:\w+=("|\').*?("|\')/', '', $html);
        // Удаляем пустые <![CDATA[]]>
        $html = str_replace('<![CDATA[]]>', '', $html);

        return $html;
    }

    //-----------------------------------------------------------------------------

    /**
     * Process form actions
     */
    public function processActions()
    {
        $Eresus = Eresus_CMS::getLegacyKernel();

        $this->parse();
        $actionsElement = $this->xml->getElementsByTagNameNS(self::NS, 'actions');

        if ($actionsElement)
        {
            $actions = $actionsElement->item(0)->childNodes;
            for ($i = 0; $i < $actions->length; $i++)
            {
                $action = $actions->item($i);
                if ($action->nodeType == XML_ELEMENT_NODE)
                {
                    $this->processAction($action);
                }
            }
        }

        if ($this->redirect)
        {
            HTTP::redirect($this->redirect);
        }
        if (!$this->html)
        {
            HTTP::redirect($Eresus->request['referer']);
        }
        return $this->html;
    }

    //-----------------------------------------------------------------------------

    /**
     * Производит разбор кода формы
     *
     * @return void
     *
     * @since 1.01
     */
    protected function parse()
    {
        $code = $this->owner->getForms()->getFormCode($this->name);

        if ($code)
        {
            $imp = new DOMImplementation;
            $dtd = $imp->createDocumentType('html', '-//W3C//DTD XHTML 1.0 Strict//EN',
                'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd');
            $this->xml = $imp->createDocument("", "", $dtd);
            $Eresus = Eresus_CMS::getLegacyKernel();
            $code =
                '<!DOCTYPE root[' .
                file_get_contents($Eresus->froot . 'core/xhtml-lat1.ent') .
                file_get_contents($Eresus->froot . 'core/xhtml-special.ent') .
                ']>' .
                $code;
            $this->xml->loadXML($code);
            $this->xml->encoding = 'utf-8';
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
        $Eresus = Eresus_CMS::getLegacyKernel();

        /** @var DOMElement $form */
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
        $input->setAttribute('value', $this->owner->name);
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
     * Get element's 'label' attribute
     *
     * @param DOMElement $element
     * @return string
     */
    protected function getLabelAttr($element)
    {
        $label = $element->getAttributeNS(self::NS, 'label');
        return $label;
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
        $skipNames = array('ext', 'form');

        /** @var DOMElement $element */
        $element = $this->xml->getElementsByTagName('form')->item(0);
        $elements = $element->getElementsByTagName('*');

        for ($i = 0; $i < $elements->length; $i++)
        {
            $element = $elements->item($i);

            $isElement = $element->nodeType == XML_ELEMENT_NODE;
            $isInputTag = $isElement && in_array($element->nodeName, $inputTagNames);

            if ($isInputTag)
            {
                $name = $element->getAttribute('name');
                if (in_array($name, $skipNames))
                {
                    continue;
                }
                if ($name)
                {
                    if ($element->getAttribute('type') == 'file')
                    {
                        $data[$name]['file'] = $_FILES[$name];
                    }
                    else
                    {
                        $data[$name]['data'] = arg($name);
                    }
                    $data[$name]['label'] = $this->getLabelAttr($element);
                    if (!$data[$name]['label'])
                    {
                        $data[$name]['label'] = $name;
                    }

                    switch ($element->nodeName)
                    {
                        case 'input':
                            switch ($element->getAttribute('type'))
                            {
                                case 'checkbox':
                                    $data[$name]['data'] = $data[$name]['data'] ? strYes : strNo;
                                    break;
                            }
                            break;
                    }
                }
            }
        }

        return $data;
    }

    //-----------------------------------------------------------------------------

    /**
     * Process action directive
     *
     * @param DOMNode $action
     */
    protected function processAction(DOMNode $action)
    {
        $actionName = substr($action->nodeName, strlen($action->lookupPrefix(self::NS)) + 1);
        $methodName = 'action' . $actionName;
        if (method_exists($this, $methodName))
        {
            $this->$methodName($action);
        }
    }

    //-----------------------------------------------------------------------------

    /**
     * Выполняет действие 'mailto'
     *
     * @param DOMElement $action
     *
     * @uses EForms::verifyClassLoaded()
     */
    protected function actionMailto($action)
    {
        $mail = new EForms_Mail();

        if (!($to = $action->getAttribute('to')))
        {
            return;
        }
        $mail->addTo($to);

        if (!($subj = $action->getAttribute('subj')))
        {
            $subj = $this->name;
        }
        $mail->setSubject($subj);

        $data = $this->getFormData();

        $text = '';
        foreach ($data as $name => $item)
        {
            if (!isset($item['label']))
            {
                continue;
            }
            if (isset($item['data']))
            {
                $text .= $item['label'] . ': ' . $item['data'] . "\n";
            }
            elseif (isset($item['file']))
            {
                $Eresus = Eresus_CMS::getLegacyKernel();
                $filename = tempnam($Eresus->fdata, $this->owner->name);
                if ($filename = upload($name, $filename, true))
                {
                    list ($contentType, $mimeType) = explode('/', $item['file']['type']);
                    $mail->addAttachment($item['file']['name'], file_get_contents($filename), $contentType,
                        $mimeType);
                    unlink($filename);
                }
            }
        }
        $mail->setText($text);
        $mail->send();
    }

    //-----------------------------------------------------------------------------

    /**
     * Process 'redirect' action
     *
     * @param DOMElement $action
     */
    protected function actionRedirect($action)
    {
        if ($this->redirect)
        {
            return;
        }

        $this->redirect = $action->getAttribute('uri');
        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $this->redirect = $page->replaceMacros($this->redirect);
    }

    //-----------------------------------------------------------------------------

    /**
     * Process 'html' action
     *
     * @param DOMElement $action
     */
    protected function actionHtml($action)
    {
        $elements = $action->childNodes;

        if ($elements->length)
        {
            $html = '';
            for ($i = 0; $i < $elements->length; $i++)
            {
                $html .= $this->xml->saveXML($elements->item($i));
            }
            $this->html .= $html;
        }
    }
    //-----------------------------------------------------------------------------
}
