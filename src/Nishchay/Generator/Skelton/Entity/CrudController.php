<?php

namespace Nishchay\Generator\Skelton\Entity;

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
     * @Placeholder(id='string')
     */
    public function create($id = '@Segment(index=id)')
    {
        
    }

    /**
     * Used for viewing record.
     * 
     * @Route(path='{id}', type=GET)
     * @Placeholder(id='string')
     */
    public function fetch($id = '@Segment(index=id)')
    {
        // TODO: Fetch record
    }

    /**
     * Used for updating record.
     * 
     * @Route(path='{id}', type=PUT)
     * @Placeholder(id='string')
     */
    public function update($id = '@Segment(index=id)')
    {
        
    }

    /**
     * Used for deleting record.
     * @Route(path='{id}', type=DELETE)
     * @Placeholder(id='string')
     */
    public function delete($id = '@Segment(index=id)')
    {
        
    }

}
