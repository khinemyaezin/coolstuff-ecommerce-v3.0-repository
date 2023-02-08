<?php

namespace App\Models;

use DateTimeInterface;

trait CsModel
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y h:i:s A');
    }
}