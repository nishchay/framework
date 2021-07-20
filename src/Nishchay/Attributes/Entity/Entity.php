<?php

namespace Nishchay\Attributes\Entity;

use \Nishchay\Attributes\AttributeTrait;
use Nishchay\Utility\StringUtility;

/**
 * Description of Entity
 *
 * @author bhavik
 */
#[\Attribute]
class Entity
{

    use AttributeTrait {
        verify as parentVerify;
    }

    const NAME = 'entity';

    /**
     * Static data table name.
     */
    const STATIC_TABLE_NAME = 'StaticData';

    /**
     * Reserved entity names.
     * 
     * @var array 
     */
    private $reserved = [self::STATIC_TABLE_NAME];

    public function __construct(private string $name,
            private string $case = 'same', private string $separator = '_')
    {
        
    }

    public function verify()
    {
        $this->parentVerify();
        $this->case = strtolower($this->case);
        if ($this->name === 'this' || $this->name === 'this.base') {
            if (strpos($this->name, 'base')) {
                $this->name = StringUtility::getExplodeLast('\\', $this->class);
            } else {
                $this->name = str_replace(['\\'], $this->separator, $this->class);
            }
        }

        $callback = [
            'lower' => 'strtolower',
            'upper' => 'strtoupper',
            'camel' => 'lcfirst'
        ];
        $this->name = (array_key_exists($this->case, $callback) ?
                call_user_func($callback[$this->case], $this->name) :
                $this->name);

        # Preventing some reserved entity names which should not be used.
        if (in_array(strtolower($this->name), $this->reserved)) {
            throw new NotSupportedException('[' . $this->name . '] is reserved'
                            . ' entity name.', $this->class, null, 911032);
        }

        return $this;
    }

}
