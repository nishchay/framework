<?php

namespace Nishchay\Generator\Skelton\Controller;

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
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        }

        return false;
    }

    /**
     * Returns mapping for hostel controller template.
     * 
     * @return array
     */
    private function getHostel()
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
    private function getEmployee()
    {
        return [
            'employee' => Employee\Employee::class,
            'attendance' => Employee\Attendance::class
        ];
    }

}
