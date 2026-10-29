<?php

namespace App\Enums;

enum DomainStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
}
