<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Saritasa\Laravel\Controllers\Responses\AddressDTO;

class AddressDtoTest extends TestCase
{
    function testConstruct()
    {
        $address = new AddressDTO([
            'name' => 'Darth Vader',
            'address' => 'Capitans\'s Bridge',
            'city'  => 'Death Star',
            'state' => 'Galaxy Empire',
            'zip'   => '00001'
        ]);

        self::assertEquals('Darth Vader', $address->name);
        self::assertEquals('Capitans\'s Bridge', $address->address);
        self::assertEquals('Death Star', $address->city);
        self::assertEquals('Galaxy Empire', $address->state);
        self::assertEquals('00001', $address->zip);
    }
}
