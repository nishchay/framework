<?php

namespace Nishchay\Generator\Skelton\Controller\Hostel;

use Nishchay\Attributes\Controller\Controller;
use Nishchay\Attributes\Controller\Routing;
use Nishchay\Attributes\Controller\Method\{
    Route,
    Placeholder
};

/**
 * Hostel Building room controller class.
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
class Room
{

    /**
     * View hostel building room detail.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}/room/{roomId}', type: 'GET')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int', 'roomId' => 'int'])]
    public function view(int $hostelId, int $buildingId, int $roomId)
    {
        # TODO: Display building detail
    }

    /**
     * Add room to building.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}/room', type: 'POST')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int'])]
    public function create(int $hostelId, int $buildingId)
    {
        # TODO: ADD room to hostel building
    }

    /**
     * Edit hostel building room detail.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}/room/{roomId}', type: 'PUT')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int', 'roomId' => 'int'])]
    public function update(int $hostelId, int $buildingId, int $roomId)
    {
        # TODO: Update room detail
    }

    /**
     * Remove room.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}/room/{roomId}',
                type: 'DELETE')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int', 'roomId' => 'int'])]
    public function remove(int $hostelId, int $buildingId, int $roomId)
    {
        # TODO: Remove rooom.
    }

    /**
     * View list of guests in hostel building room.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}/room/{roomId}/guest',
                type: 'GET')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int', 'roomId' => 'int'])]
    public function viewRoomGuests(int $hostelId, int $buildingId, int $roomId)
    {
        # TODO: Display list of guest staying in room.
    }

}
