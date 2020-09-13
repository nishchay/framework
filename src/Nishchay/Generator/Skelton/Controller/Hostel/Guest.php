<?php

namespace Nishchay\Generator\Skelton\Controller\Hostel;

/**
 * Hostel guest controller class.
 *
 * #ANN_START
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * #ANN_END
 * {authorName}
 * {versionNumber}
 * @Controller
 * @Routing(prefix='hostel')
 */
class Guest
{

    /**
     * Display list of guests staying in hostel.
     * 
     * @Route(path='{hostelId}/guests', type=GET)
     * @Placeholder(hostelId=number)
     * @Response(type=VIEW)
     */
    public function index(int $hostelId)
    {
        # TODO: Diplay list of guests staying in hostel
    }

    /**
     * View hostel guest detail.
     * 
     * @Route(path='{hostelId}/guest/{guestId}', type=GET)
     * @Placeholder(hostelId=number, guestId=number)
     * @Response(type=VIEW)
     */
    public function view(int $hostelId, int $guestId)
    {
        # TODO: Display hostel guest detail
    }

    /**
     * Add guest to hostel.
     * 
     * @Route(path='{hostelId}/guest', type=POST)
     * @Placeholder(hostelId=number)
     * @Response(type=VIEW)
     */
    public function create(int $hostelId)
    {
        # Add guest to hostel.
        # You might need
        # 1. Building ID
        # 2. Room ID.
        # 3. Guest Details.
    }

    /**
     * Update hostel guests.
     * 
     * @Route(path='{hostelId}/guest/{guestId}', type=PUT)
     * @Placeholder(hostelId=number, guestId=number)
     * @Response(type=VIEW)
     */
    public function update(int $hostelId, int $guestId)
    {
        # Update hostel guest detail.
        /**
         * YOu might need
         *  1. Building ID
         *  2. Guest Details.
         *  3. Room ID.
         */
    }

    /**
     * Remove hostel guest.
     * 
     * @Route(path='{hostelId}/guest/{guestId}', type=DELETE)
     * @Placeholder(hostelId=number, guestId=number)
     * @Response(type=VIEW)
     */
    public function remove(int $hostelId, int $guestId)
    {
        # Remove guest from hostel
    }

    /**
     * Pay guest fees.
     * 
     * @Route(path='{hostelId}/guests/{guestId}/fees', type=POST)
     * @Placeholder(hostelId=number,guestId=number)
     */
    public function guestFees(int $hostelId, int $guestId)
    {
        # TODO: Pay guest fees
    }

    /**
     * View guest fee receipt.
     * 
     * @Route(path='{hostelId}/guests/{guestId}/fees/{feesId}')
     * @Placeholder(hostelId=number,guestId=number,feesId=number)
     */
    public function guestFeesDetail(int $hostelId, int $guestId, int $feesId)
    {
        # TODO: 
        # View guest fees detail.
        # You can implement this to view guest feess receipt.
    }

}
