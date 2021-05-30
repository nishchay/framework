<?php

namespace Nishchay\Console;

use Nishchay\Console\Printer;

/**
 * Description of Help
 *
 * @author bpatel
 */
class Help
{

    private $helps = [
        'route' => [
            '{route-alias}' => 'r',
            '{no-params}' => 'To list all defined routes',
            'path' => 'Gives information of route',
            'path -run' => 'Executes route',
            '-name name' => 'Gives information based on path as specified in Route attribute',
            '-match pattern' => 'Lists routes which matches pattern',
            '-controller name' => 'Lists routes defined in controller name',
            '-context name' => 'Lists routes belongs to context name',
            'path -event' => 'Lists events for route',
            'path -handler' => 'Lists error handler for route',
        ],
        'controller' => [
            '{controller-alias}' => 'c',
            '{no-params}' => 'List all controller',
            'class' => 'Gives information of controller',
            '-create name' => 'Creates empty controller',
            '-create name -crud' => 'Creates contrller with crud routes',
            '-create name -template name' => 'Creates controller from template name'
        ],
        'event' => [
            '{event-alias}' => 'ev',
            '{no-params}' => 'Lists all events',
            '-context name' => 'Lists events belongs to context',
            '-scope' => 'Lists events belongs to scope',
            '-global' => 'Lists global events'
        ],
        'entity' => [
            '{entity-alias}' => 'e',
            '{no-params}' => 'Lists all entities',
            'name -property' => 'Lists entity properties',
            'name -derived' => 'Lists derived properties',
            'name -identity' => 'Gives information about entity identity',
            'name -trigger' => 'Lists all trigger of entity',
            '-create name' => 'Creates entity with identity property only',
            '-create name -crud' => 'Creates entity crud related properties',
            '-create name -template name' => 'Creates entity from template',
            '-generate -new' => 'Creates entity. Interactive command',
            '-generate -db (?connectionName)' => 'Creates all or specific entities from DB. Do not pass connection name to use default database connection.',
            '-generate -table name (?connectionName)' => 'Create entity for given table name. Do not pass connection name to use default database connection.'
        ],
        'form' => [
            '{form-alias}' => 'f',
            '-generate -entity name' => 'Generates form from entity class name'
        ],
        'prototype' => [
            '{prototype-alias}' => 'p',
            '-generate -new' => 'Generates new prototype based on interactive command',
            '-generate -table name (?connectionName)' => 'Generates prototype from table. Do not pass connection name to use default database connection.'
        ],
        '' => [
            'version' => 'Prints framework version information',
            '-v' => 'Alias of version',
            '-version' => 'Alias of version'
        ],
    ];

    /**
     * 
     */
    public function __construct($helpFor = false)
    {
        $this->printHelp($helpFor);
    }

    /**
     * 
     */
    private function printHelp($helpFor)
    {
        foreach ($this->helps as $commandName => $subCommands) {
            if ($helpFor !== false && $helpFor !== $commandName) {
                continue;
            }
            Printer::write($commandName, Printer::YELLOW_COLOR);
            Printer::write(PHP_EOL);
            foreach ($subCommands as $subCommandName => $helpLine) {
                if (strpos($subCommandName, '{') === 0) {
                    $color = Printer::GREY_COLOR;
                } else {
                    $color = Printer::GREEN_COLOR;
                }
                Printer::write(str_pad($subCommandName, 40), $color);
                Printer::write($helpLine . PHP_EOL);
            }
            Printer::write(PHP_EOL);
        }
    }

}
