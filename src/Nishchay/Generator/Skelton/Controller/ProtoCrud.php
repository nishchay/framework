<?php

namespace Nishchay\Generator\Skelton\Controller;

use Nishchay\Prototype\Crud;
use Nishchay\Form\Form;
use Nishchay\Generator\Entity;
use Nishchay\Processor\FetchSingletonTrait;
use Nishchay\Attributes\Controller\Controller;
use Nishchay\Attributes\Controller\Routing;
use Nishchay\Attributes\Controller\Method\{
    Route,
    Placeholder,
    Response
};

/**
 * {ProtoCrudClassDescription}
 *
 * #ANN_START
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @author      Bhavik Patel
 * #ANN_END
 * {authorName}
 * {versionNumber}
 * 
 */
#[Controller]
#[Routing(prefix: '#routeName#')]
class ProtoCrud
{

    use FetchSingletonTrait;

    /**
     * Returns instance of Crud prototype.
     * 
     * @return Crud
     */
    private function getPrototype()
    {
        return $this->getInstance(Crud::class, [Entity::class]);
    }

    /**
     * Used for listing.
     * 
     */
    #[Route(path: '/', type: 'GET')]
    #[Response(type: 'json')]
    public function index()
    {
        return $this->getPrototype()
                        ->get();
    }

    /**
     * Used for inserting record.
     * 
     */
    #[Route(path: '/', type: 'POST')]
    #[Response(type: 'json')]
    public function create()
    {
        return $this->getPrototype()
                        ->setForm(Form::class)
                        ->insert();
    }

    /**
     * Used for viewing record.
     * 
     */
    #[Route(path: '{id}', type: 'GET')]
    #[Placeholder(['id' => 'int'])]
    #[Response(type: 'json')]
    public function fetch(int $id)
    {
        return $this->getPrototype()
                        ->getOne($id);
    }

    /**
     * Used for updating record.
     * 
     */
    #[Route(path: '{id}', type: 'PUT')]
    #[Placeholder(['id' => 'int'])]
    #[Response(type: 'json')]
    public function update(int $id)
    {
        return $this->getPrototype()
                        ->setForm(Form::class)
                        ->update($id);
    }

    /**
     * Used for deleting record.
     * 
     */
    #[Route(path: '{id}', type: 'DELETE')]
    #[Placeholder(['id' => 'int'])]
    #[Response(type: 'json')]
    public function delete(int $id)
    {
        return $this->getPrototype()
                        ->remove($id);
    }

}
