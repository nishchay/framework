<?php

namespace Nishchay\Generator\Skelton\Controller\Hostel;

use Nishchay\Attributes\Controller\Controller;
use Nishchay\Attributes\Controller\Routing;
use Nishchay\Attributes\Controller\Method\{
    Route,
    Placeholder
};

/**
 * Hostel Building controller class.
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
class Building
{

    /**
     * Lists building belongs to hostel
     * 
     */
    #[Route(path: '{hostelId}/building', type: 'GET')]
    #[Placeholder(['hostelId' => 'int'])]
    public function index(int $hostelId)
    {
        # TODO: Display list of building belongs to hostel
    }

    /**
     * Display hostel building detail.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}', type: 'GET')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int'])]
    public function view(int $hostelId, int $buildingId)
    {
        # TODO: Display hostel building detail
    }

    /**
     * Add building.
     * 
     */
    #[Route(path: '{hostelId}/building', type: 'POST')]
    #[Placeholder(['hostelId' => 'int'])]
    public function create(int $hostelId)
    {
        # TODO: Add building to hostel
    }

    /**
     * Update Building.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}', type: 'PUT')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int'])]
    public function update(int $hostelId, int $buildingId)
    {
        # TODO: Update building detail.
    }

    /**
     * Remove building.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}', type: 'DELETE')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int'])]
    public function remove(int $hostelId, int $buildingId)
    {
        # TODO: Remove building from hostel.
    }

    /**
     * Display list of guests in hostel building.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}/guests', type: 'GET')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int'])]
    public function guests(int $hostelId, int $buildingId)
    {
        # TODO: Display list of guests residing in hostel building
    }

    /**
     * Display various fees for given building.
     * 
     */
    #[Route(path: '{hostelId}/building/{buildingId}/fees', type: 'GET')]
    #[Placeholder(['hostelId' => 'int', 'buildingId' => 'int'])]
    public function fees(int $hostelId, int $buildingId)
    {
        # TODO: Display list of fees for hostel building
    }

}
