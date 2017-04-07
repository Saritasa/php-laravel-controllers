<?php

namespace Saritasa\Laravel\Controllers\Responses;

use App\Models\Preference;
use Saritasa\Transformers\DtoModel;

class AddressDTO extends DtoModel
{
    protected $name;
    protected $address;
    protected $city;
    protected $state;
    protected $zip;
}