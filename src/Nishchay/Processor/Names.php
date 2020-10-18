<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nishchay\Processor;

/**
 *
 * @author bpatel
 */
interface Names
{

    /**
     * Global name.
     */
    const TYPE_GLOBAL = 'global';

    /**
     * Scope name.
     */
    const TYPE_SCOPE = 'scope';

    /**
     * Context name.
     */
    const TYPE_CONTEXT = 'context';

    /**
     * Encryption type PHP for entity.
     */
    const ENCRYPTION_PHP = 'php';

    /**
     * Encryption type DB for entity.
     */
    const ENCRYPTION_DB = 'db';

}
