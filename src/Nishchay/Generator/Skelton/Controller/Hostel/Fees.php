<?php

namespace Nishchay\Generator\Skelton\Controller\Hostel;

use Nishchay\Attributes\Controller\Controller;
use Nishchay\Attributes\Controller\Routing;
use Nishchay\Attributes\Controller\Method\{
    Route,
    Placeholder
};

/**
 * Hostel fees controller class.
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
class Fees
{

    /**
     * View various hostel fees.
     * 
     */
    #[Route(path: '{hostelId}/fees', type: 'GET')]
    #[Placeholder(['hostelId' => 'int'])]
    public function viewHostelFeesList(int $hostelId)
    {
        # TODO: Display various hostel fees
    }

    /**
     * View guests fees.
     * 
     */
    #[Route(path: '{hostelId}/guests/{guestId}/fees', type: 'GET')]
    #[Placeholder(['hostelId' => 'int', 'guestId' => 'int'])]
    public function viewGuestFeesList(int $hostelId, int $guestId)
    {
        # TODO: Display fees paid by guest or can also display
        # fees pending to paid
    }

}
