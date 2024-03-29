<?php

namespace Nishchay\Generator\Skelton\Controller\Hostel;

use Nishchay\Attributes\Controller\Controller;
use Nishchay\Attributes\Controller\Routing;
use Nishchay\Attributes\Controller\Method\{
    Route,
    Placeholder
};

/**
 * Hostel controller class.
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
 */
#[Controller]
#[Routing(prefix: 'hostel')]
class Hostel
{

    /**
     * List of hostels.
     * 
     */
    #[Route(path: '/', type: 'GET')]
    public function index()
    {
        # Display list of hostels or you can create dashboard
    }

    /**
     * View hostel detail.
     * You may list fees, building, room, furniture, mess and student.
     * 
     */
    #[Route(path: '{hostelId}', type: 'GET')]
    #[Placeholder(['hostelId' => 'int'])]
    public function view(int $hostelId)
    {
        # Display hostel detail
    }

    /**
     * Add hostel.
     * 
     */
    #[Route(path: '/', type: 'POST')]
    public function create()
    {
        # Create new hostel
    }

    /**
     * Edit hostel detail.
     * 
     */
    #[Route(path: '{hostelId}', type: 'PUT')]
    #[Placeholder(['hostelId' => 'int'])]
    public function update(int $hostelId)
    {
        # Update hostel detail
    }

    /**
     * Remove hostel.
     * 
     */
    #[Route(path: '{hostelId}', type: 'DELETE')]
    #[Placeholder(['hostelId' => 'int'])]
    public function remove(int $hostelId)
    {
        # Remove hostel
    }

}
