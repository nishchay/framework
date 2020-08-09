<?php

namespace Nishchay\Generator\Skelton\Controller\Employee;

/**
 * Employee controller class.
 *
 * #ANN_START
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * #ANN_END
 * {authorName}
 * {versionNumber}
 * 
 * @Controller
 * @Routing(prefix='employee')
 */
class Employee
{

    /**
     * Display list of employees.
     * 
     * @Route(path='/', type=GET)
     */
    public function index()
    {
        # TODO: Display list of employee
        # 
        # Can do following things
        # 1. Filter listing
        # 2. Search employee
    }

    /**
     * Display employee detail.
     * 
     * @Route(path='{employeeId}', type=GET)
     * @Placeholder(employeeId=number)
     */
    public function view()
    {
        # TODO: Display employee detail.
    }
    
    /**
     * Add employee.
     * 
     * @Route(path='/', type=POST)
     */
    public function create($employeeId = '@Segment(index=employeeId)')
    {
        # TODO: Create new employee
    }

    /**
     * Edit employee detail.
     * 
     * @Route(path='{employeeId}', type=PUT)
     * @Placeholder(employeeId=number)
     */
    public function update($employeeId = '@Segment(index=employeeId)')
    {
        # TODO: Update employee detail
    }

    /**
     * Remove employee.
     * 
     * @Route(path='{employeeId}', type=DELETE)
     * @Placeholder(employeeId=number)
     */
    public function remove($employeeId = '@Segment(index=employeeId)')
    {
        # TODO: Remove employee
    }

}
