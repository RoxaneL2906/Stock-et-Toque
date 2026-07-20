<?php

namespace App\Enum;

enum VisibiliteEnum: string
{
    case PUBLIQUE = 'publique';
    case PRIVEE = 'privee';
    case ANONYME = 'anonyme';
}
