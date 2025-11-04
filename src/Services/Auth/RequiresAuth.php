<?php

namespace App\Services\Auth;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class RequiresAuth
{
    public const REQUEST_ATTRIBUTE = '_bagatelle_auth';
}