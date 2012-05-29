<?php

namespace Core\Model;

class Email extends \Core\Abstracts\Singleton 
{
	private $_mailer = false;

	public function __construct()
	{
		require_once APP . '/library/Swift/lib/swift_required.php';

		$mail = $this->env->variables->MAIL;

		if (!$mail)
		{
			$this->page->setMessage('Произошла ошибка: почтовый клиент не сконфигурирован!');
		}
		else 
		{
			$transport = \Swift_SmtpTransport::newInstance($mail->host, 25)
				->setUsername($mail->user)
				->setPassword($mail->password);

			if (!empty($mail->port))
			{
				$transport->setPort($mail->port);
			}

			if (!empty($mail->encryption))
			{
				$transport->setEncryption($mail->encryption);
			}

			$this->_mailer = \Swift_Mailer::newInstance($transport);
		}
	}

	static function validate($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public function create($options = false)
	{
		$result = false;

		if ($this->_mailer)
		{
			$message = \Swift_Message::newInstance()->setCharset('utf-8');;

			$subject = empty($options['subject'])
				? 'Вам письмо!'
				: $options['subject'];
			
			$message->setSubject($subject);

			$priority = empty($options['prority'])
				? 5
				: $options['priority'];
			
			$message->setPriority($priority);

			$from = empty($options['from'])
				? array($this->env->variables->MAIL->sender => 'Мейлер')
				: $options['from'];

			$message->setFrom($from);

			$to = empty($options['to'])
				? array('ilya@weboshin.ru' => 'Ilya Doroshin') # чтобы на меня валились пустые письма!
				: $options['to'];

			$message->setTo($to);

			if (!empty($options['cc']))
			{
				$message->setCc($options['cc']);
			}

			$text = empty($options['text'])
				? ''
				: $options['text'];

			$is_html = empty($options['is_plain'])
				? 'text/html'
				: 'text/plain';

			$message->addPart($text, $is_html);

			$result = $this->_mailer->send($message);	
		}

		return $result;
	}

	static function check($email, $skipDNS = false)
	{
		$isValid = true;
		$atIndex = strrpos($email, "@");
		
		if (is_bool($atIndex) && !$atIndex)
		{
			$isValid = false;
		}
		else
		{
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);

			if ($localLen < 1 || $localLen > 64)
			{
				// local part length exceeded
				$isValid = false;
			}
			else if ($domainLen < 1 || $domainLen > 255)
			{
				// domain part length exceeded
				$isValid = false;
			}
			else if ($local[0] == '.' || $local[$localLen-1] == '.')
			{
				// local part starts or ends with '.'
				$isValid = false;
			}
			else if (preg_match('/\\.\\./', $local))
			{
				// local part has two consecutive dots
				$isValid = false;
			}
			else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
			{
				// character not valid in domain part
				$isValid = false;
				}
			else if (preg_match('/\\.\\./', $domain))
			{
				// domain part has two consecutive dots
				$isValid = false;
			}
			else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
			{
				// character not valid in local part unless 
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local)))
				{
					$isValid = false;
				}
			}

			if(!$skipDNS)
			{
				if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
				{
					// domain not found in DNS
					$isValid = false;
				}
			}
		}
		return $isValid;
	}	
}