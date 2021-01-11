<?php

namespace Nishchay\Generator\Skelton\Controller;

use Nishchay\Utility\MethodInvokerTrait;

/**
 * Template mapper class controller.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class TemplateMapper
{

    use MethodInvokerTrait;

    /**
     * Returns template mapping to class if it does exist.
     * 
     * @param string $template
     * @return array
     * @throws \Exception
     */
    public function getMapping($template)
    {
        $method = 'get' . ucfirst(strtolower($template));
        if ($this->isCallbackExist([$this, $method])) {
            return $this->invokeMethod([$this, $method]);
        }

        return false;
    }

    /**
     * Returns mapping for hostel controller template.
     * 
     * @return array
     */
    private function getHostel(): array
    {
        return [
            'hostel' => Hostel\Hostel::class,
            'fees' => Hostel\Fees::class,
            'building' => Hostel\Building::class,
            'room' => Hostel\Room::class,
            'guest' => Hostel\Guest::class
        ];
    }

    /**
     * Returns mapping for employee controller template.
     * 
     * @return array
     */
    private function getEmployee(): array
    {
        return [
            'employee' => Employee\Employee::class,
            'attendance' => Employee\Attendance::class
        ];
    }

    /**
     * Returns mapping for post controller template.
     * 
     * @return array
     */
    private function getPost(): array
    {
        return [
            'post' => Post::class
        ];
    }

    /**
     * Returns mapping for message controller template.
     * 
     * @return array
     */
    private function getMessage(): array
    {
        return [
            'message' => Message::class
        ];
    }

}
