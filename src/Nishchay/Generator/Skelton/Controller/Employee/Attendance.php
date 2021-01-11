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
class Attendance
{

    /**
     * Displays attendance of an employee for current month or trend.
     * 
     * @Route(path='attendance', type=GET)
     */
    public function index()
    {
        # TODO: You can display attendance of current month or
        # week or current trend
    }

    /**
     * Displays attendance of an employee for requested month and year.
     * 
     * @Route(path='attendance/{year}/{month}', type=GET)
     * @Placeholder(year=number, month=number)
     */
    public function monthAttendance(int $year, int $month)
    {
        # TODO: Display employee's attendance of requested month and year
    }

    /**
     * Log employee attendance.
     * 
     * @Route(path='attendance', type=POST)
     */
    public function create()
    {
        # TODO: Implement business to log employee attedance
        # 
        # Insert each entry into table then fetch first being login time
        # and last entry being logout time.
    }

    /**
     * Display list of request.
     * 
     * @Route(path='attendance/request', type=GET)
     */
    public function request()
    {
        # TODO: Display list request.
        # 
        # This list should be based on logged user's rights
    }

    /**
     * Request for missed login or logout time.
     * 
     * @Route(path='attendance/request', type=POST)
     */
    public function createRequest()
    {
        # TODO: Implement business to request login and logout time.
    }

    /**
     * View to attendance request.
     * 
     * @Route(path='attendance/request/{requestId}', type=POST)
     * @Placeholder(requestId=number)
     */
    public function viewRequest(int $requestId)
    {
        # TODO: Implement business to respond attedance request.
    }

    /**
     * Responds to attendance request.
     * 
     * @Route(path='attendance/request/{requestId}/respond', type=POST)
     * @Placeholder(requestId=number)
     */
    public function responseRequest(int $requestId)
    {
        # TODO: Implement business to respond attedance request.
    }

}
