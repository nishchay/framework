<?php

namespace Nishchay\Generator\Skelton\Controller;

/**
 * {CrudControllerClassDescription}
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
 * @Routing(prefix='#routeName#')
 */
class CrudController
{

    /**
     * Used for listing.
     * 
     * @Route(path='/', type=GET)
     */
    public function index()
    {
        // TODO: Listing
    }

    /**
     * Used for inserting record.
     * 
     * @Route(path='/', type=POST)
     */
    public function create()
    {
        // TODO: Insert record
    }

    /**
     * Used for viewing record.
     * 
     * @Route(path='{id}', type=GET)
     * @Placeholder(id=number)
     */
    public function fetch(int $id)
    {
        // TODO: Fetch record
    }

    /**
     * Used for updating record.
     * 
     * @Route(path='{id}', type=PUT)
     * @Placeholder(id=number)
     */
    public function update(int $id)
    {
        // TODO: Update record
    }

    /**
     * Used for deleting record.
     * 
     * @Route(path='{id}', type=DELETE)
     * @Placeholder(id=number)
     */
    public function delete(int $id)
    {
        // TODO: Delete record
    }

}
