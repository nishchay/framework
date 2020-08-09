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
            '{no-params}' => 'To list all defined routes',
            'path' => 'Gives information of route',
            'path -run' => 'Executes route',
            '-name name' => 'Gives information based on path as defined in @Route annotation',
            '-match pattern' => 'Lists routes which matches pattern',
            '-controller name' => 'Lists routes defined in controller name',
            '-context name' => 'Lists routes belongs to context name',
            'path -event' => 'Lists events for route',
            'path -handler' => 'Lists error handler for route',
        ],
        'controller' => [
            '{no-params}' => 'List all controller',
            'class' => 'Gives information of controller',
            'class -annotation' => 'Lists annotation defined on class',
            'class -annotation -method method' => 'Lists annotation defined on controller method',
            '-create name' => 'Creates empty controller',
            '-create name -crud' => 'Creates contrller with crud routes',
            '-create name -template name' => 'Creates controller from template name'
        ],
        'event' => [
            '{no-params}' => 'Lists all events',
            '-context name' => 'Lists events belongs to context',
            '-scope' => 'Lists events belongs to scope',
            '-global' => 'Lists global events'
        ],
        'entity' => [
            '{no-params}' => 'Lists all entities',
            'name -property' => 'Lists entity properties',
            'name -derived' => 'Lists derived properties',
            'name -identity' => 'Gives information about entity identity',
            'name -trigger' => 'Lists all trigger of entity',
            '-create name' => 'Creates entity with identity property only',
            '-create name -crud' => 'Creates entity crud related properties',
            '-create name -template name' => 'Creates entity from template'
        ]
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
