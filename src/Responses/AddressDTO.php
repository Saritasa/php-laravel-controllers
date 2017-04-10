<?php

namespace Saritasa\Laravel\Controllers\Responses;

use App\Models\Preference;
use Saritasa\Transformers\DtoModel;

/**
 * Address
 *
 * @property-read string $name Person full name
 * @property-read string $address Street address
 * @property-read string $city City
 * @property-read string $state State
 * @property-read string $zip Zip Code
 */
class AddressDTO extends DtoModel
{
    protected $name;
    protected $address;
    protected $city;
    protected $state;
    protected $zip;
}
