<?php

namespace Nishchay\Mail;

use Processor;
use Nishchay\Exception\ApplicationException;
use Swift_Mailer;
use Swift_SmtpTransport;
use Swift_Attachment;
use Swift_Image;
use Swift_Message;
use Nishchay\Http\View\ViewHandler;

/**
 * Description of Mail
 *
 * @author bpatel
 */
class Mail
{

    /**
     *
     * @var \Nishchay\Mail\MailMessage
     */
    private $message;

    /**
     *
     * @var \Swift_SmtpTransport
     */
    private $transport;

    /**
     *
     * @var \stdClass
     */
    private $config;

    /**
     * List of attachments.
     * 
     * @var array
     */
    private $attachments = [];

    /**
     *
     * @var ViewHandler
     */
    private $viewHandler;

    /**
     * Initialization.
     * 
     * @param Swift_Transport $transport
     * @param type $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * Initialization.
     * 
     */
    private function init()
    {
        if (!isset($this->config->host) || !isset($this->config->port) ||
                !isset($this->config->credential)) {
            throw new ApplicationException('Mail configuration host, port or'
                    . ' credential not set.', null, null, 923002);
        }

        $this->config->encryption = $this->config->encryption ?? null;
        $this->transport = new Swift_SmtpTransport($this->config->host, $this->config->port, $this->config->encryption);

        $credential = $this->config->credential;

        if (!isset($credential->username) || !isset($credential->password)) {
            throw new ApplicationException('Mail credential username or'
                    . ' password not set.', null, null, 923003);
        }

        $this->transport->setUsername($credential->username);
        $this->transport->setPassword($credential->password);
    }

    /**
     * Returns mail message.
     * 
     * @return \Swift_Message
     */
    public function getMessage()
    {
        if ($this->message !== null) {
            return $this->message;
        }

        $this->message = new Swift_Message();

        if (!isset($this->config->from)) {
            throw new ApplicationException('Mail to be send from email'
                    . ' address not set in mail configuration.', null, null, 923004);
        }

        $this->message->setFrom($this->config->from);
        return $this->message;
    }

    /**
     * 
     * @param type $addresses
     * @return $this
     */
    public function setFrom($addresses)
    {
        $this->getMessage()->setFrom($addresses);
        return $this;
    }

    /**
     * 
     * @param type $addresses
     * @return $this
     */
    public function setTo($addresses)
    {
        $this->getMessage()->setTo($addresses);
        return $this;
    }

    /**
     * 
     * @param type $addresses
     * @return $this
     */
    public function setBcc($addresses)
    {
        $this->getMessage()->setBcc($addresses);
        return $this;
    }

    /**
     * 
     * @param type $addresses
     * @return $this
     */
    public function setCc($addresses)
    {
        $this->getMessage()->setCc($addresses);
        return $this;
    }

    /**
     * 
     * @param type $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->getMessage()->setPriority($priority);
        return $this;
    }

    /**
     * Sets body.
     * 
     * @param type $body
     * @param type $contentType
     * @param type $charset
     * @return $this
     */
    public function setBody($body, $contentType = null, $charset = null)
    {
        $this->getMessage()->setBody($body, $contentType, $charset);
        return $this;
    }

    /**
     * Sets body from view.
     * 
     * @param string $viewName
     * @param string $charset
     * @return $this
     */
    public function setBodyFromView($viewName, $charset = null)
    {
        ob_start();
        $this->getViewHandler()->render($viewName);
        return $this->setBody(ob_get_clean(), 'text/html', $charset);
    }

    /**
     * Returns view handler.
     * 
     * @return ViewHandler
     */
    private function getViewHandler()
    {
        if ($this->viewHandler !== null) {
            return $this->viewHandler;
        }

        $object = Processor::getStageDetail('object');
        return $this->viewHandler = new ViewHandler($object->getClass(), $object->getMethod(), Processor::getStageDetail('context'));
    }

    /**
     * 
     * @param type $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->getMessage()->setSubject($subject);
        return $this;
    }

    /**
     * 
     * @param type $path
     * @param type $fileName
     * @return $this
     */
    public function addAttchment($path, $fileName = null)
    {
        $attachment = Swift_Attachment::fromPath($path);

        if ($fileName !== null) {
            $attachment->setFilename($fileName);
        }
        $this->attachments[$path] = $attachment;
        $this->getMessage()->attach($attachment);
        return $this;
    }

    /**
     * 
     * @param type $path
     * @return $this
     * @throws ApplicationException
     */
    public function removeAttachment($path)
    {
        if (array_key_exists($path, $this->attachments) === false) {
            throw new ApplicationException('Attachment not found [' . $path . '].', null, null, 922005);
        }

        $this->getMessage()->detach($this->attachments[$path]);
        return $this;
    }

    /**
     * Returns embeded id to be used for embeding image to body.
     * 
     * @param string $path
     * @return string
     */
    public function embed($path)
    {
        return $this->getMessage()->embed(Swift_Image::fromPath($path));
    }

    /**
     * Checks mail to send to, mail subject, and mail body is set or not.
     * 
     * @throws ApplicationException
     */
    private function check()
    {
        if (empty($this->getMessage()->getTo())) {
            throw new ApplicationException('Mail to send [to] is not set.', null, null, 923006);
        }

        if (empty($this->getMessage()->getSubject())) {
            throw new ApplicationException('Mail subject can\' be empty.', null, null, 923007);
        }

        if (empty($this->getMessage()->getBody())) {
            throw new ApplicationException('Mail body can\'t be empty.', null, null, 923008);
        }
    }

    /**
     * Sends mail.
     * 
     * @return int
     */
    public function send()
    {
        $this->check();
        return (new Swift_Mailer($this->transport))
                        ->send($this->getMessage());
    }

}
