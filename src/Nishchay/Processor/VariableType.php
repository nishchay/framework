<?php

namespace Nishchay\Processor;

/**
 * Variable type names.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
interface VariableType
{

    /**
     * Data type int.
     */
    const INT = 'int';

    /**
     * Data type string.
     */
    const STRING = 'string';

    /**
     * Data type float.
     */
    const FLOAT = 'float';

    /**
     * Data Type double.
     */
    const DOUBLE = 'double';

    /**
     * Data type boolean.
     */
    const BOOLEAN = 'boolean';

    /**
     * Data type date only.
     */
    const DATE = 'date';

    /**
     * Data type date & time.
     */
    const DATETIME = 'datetime';

    /**
     * Data type array.
     */
    const DATA_ARRAY = 'array';

    /**
     * Data type mixed.
     */
    const MIXED = 'mixed';

}
