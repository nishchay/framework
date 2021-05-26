<?php

namespace Nishchay\Attributes\Entity;

use Nishchay\Exception\ApplicationException;
use Nishchay\Attributes\AttributeTrait;
use Nishchay\Data\Connection\Connection;

/**
 * Description of Connection
 *
 * @author bhavik
 */
#[\Attribute]
class Connect
{

    use AttributeTrait;

    const NAME = 'connect';

    public function __construct(private string $name)
    {
        
    }

    public function verify()
    {
        if (!Connection::isConnnectionExist($this->name)) {
            throw new ApplicationException('Database connection [' . $this->name . ']'
                            . ' does not exist.', $this->class, null, 911031);
        }

        return $this;
    }

}
