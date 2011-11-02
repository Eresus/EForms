<?php
/**
 * E-Forms
 *
 * @version 1.01
 *
 * @copyright 2011, Eresus Project, http://eresus.ru/
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
 * Отправка почты
 *
 * @package E-Forms
 * @since 1.00
 */
class EForms_Mail
{
	/**
	 * Объект-составитель письма
	 *
	 * @var ezcMailComposer
	 */
	private $composer;

	/**
	 * Почтовый транспорт (mail(), SMTP)
	 *
	 * @var ezcMailTransport
	 */
	private $transport;

	/**
	 * Конструктор
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 */
	public function __construct()
	{
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает составитель писем
	 *
	 * @param ezcMailComposer $composer
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMailComposer
	 */
	public function setComposer(ezcMailComposer $composer)
	{
		$this->composer = $composer;

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает составитель писем
	 *
	 * @return ezcMailComposer
	 *
	 * @since 1.00
	 * @uses ezcMailComposer
	 */
	public function getComposer()
	{
		if (!$this->composer)
		{
			$this->composer = new ezcMailComposer();
		}

		return $this->composer;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает почтовый транспорт
	 *
	 * @param ezcMailTransport $transport
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMailTransport
	 */
	public function setTransport(ezcMailTransport $transport)
	{
		$this->transport = $transport;

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает почтовый транспорт
	 *
	 * @return ezcMailTransport
	 *
	 * @since 1.00
	 * @uses ezcMailMtaTransport
	 */
	public function getTransport()
	{
		if (!$this->transport)
		{
			$this->transport = new ezcMailMtaTransport();
		}

		return $this->transport;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет получателя
	 *
	 * @param string $address  адрес получателя
	 * @param string $name     имя получателя
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMail::addTo()
	 * @uses ezcMailAddress
	 */
	public function addTo($address, $name = null)
	{
		$this->getComposer()->addTo(new ezcMailAddress($address, $name, CHARSET));

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет получателя копии
	 *
	 * @param string $address  адрес получателя
	 * @param string $name     имя получателя
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMail::addCc()
	 * @uses ezcMailAddress
	 */
	public function addCc($address, $name = null)
	{
		$this->getComposer()->addCc(new ezcMailAddress($address, $name, CHARSET));

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет получателя скрытой копии
	 *
	 * @param string $address  адрес получателя
	 * @param string $name     имя получателя
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMail::addBcc()
	 * @uses ezcMailAddress
	 */
	public function addBcc($address, $name = null)
	{
		$this->getComposer()->addBcc(new ezcMailAddress($address, $name, CHARSET));

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает отправителя
	 *
	 * @param string $address  адрес отправителя
	 * @param string $name     имя отправителя
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMailComposer::$from
	 * @uses ezcMailAddress
	 */
	public function setFrom($address, $name = null)
	{
		$this->getComposer()->from = new ezcMailAddress($address, $name, CHARSET);

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает адрес для ответа
	 *
	 * @param string $address адрес для ответа
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 */
	public function setReplyTo($address)
	{
		$this->setHeader('Reply-To', $address);
		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает заголовок
	 *
	 * @param string $subject
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMail::$subject
	 * @uses ezcMail::$subjectCharset
	 */
	public function setSubject($subject)
	{
		$this->getComposer()->subject = $subject;
		$this->getComposer()->subjectCharset = CHARSET;

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает HTML-версию текста письма
	 *
	 * @param string $html
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMailComposer::$htmlText
	 */
	public function setHTML($html)
	{
		$this->getComposer()->htmlText = $html;

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает текстовую версию текста письма
	 *
	 * @param string $text
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMailComposer::$plainText
	 */
	public function setText($text)
	{
		$this->getComposer()->plainText = $text;

		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Присоединяет файл или содержимое файла
	 *
	 * @param string $filename     путь к файлу или имя файла (см. $content)
	 * @param string $content      содержимое файла
	 * @param string $contentType  тип контента (по умолчанию "application")
	 * @param string $mimeType     тип MIME (по умолчанию "octet-stream")
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMailComposer::addAttachment()
	 */
	public function addAttachment($filename, $content = null, $contentType = null, $mimeType = null)
	{
		$this->getComposer()->addAttachment($filename, $content, $contentType, $mimeType);
		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает заголовок
	 *
	 * Если заголовок с таким именем уже есть, он будет перезаписан.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return Mailer_Mail
	 *
	 * @since 1.00
	 * @uses ezcMailPart::setHeader()
	 */
	public function setHeader($name, $value)
	{
		$this->getComposer()->setHeader($name, $value, CHARSET);
		return $this;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Отправляет письмо
	 *
	 * @return void
	 *
	 * @since 1.00
	 * @uses ezcMailComposer::build()
	 * @uses ezcMailTransport::send()
	 * @uses option()
	 */
	public function send()
	{
		$composer = $this->getComposer();
		$composer->charset = CHARSET;

		if (!$composer->from)
		{
			$composer->from = new ezcMailAddress(option('mailFromAddr'), option('mailFromName'), CHARSET);
		}

		ezcMailTools::setLineBreak("\n");
		$composer->build();
		$transport = $this->getTransport();
		// Отправляем письмо
		$transport->send($composer);
	}
	//-----------------------------------------------------------------------------
}


