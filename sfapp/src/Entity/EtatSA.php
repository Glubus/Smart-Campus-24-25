<?php

namespace App\Entity;

enum EtatSA: string
{
    case desinstalltion = 'deinstallation';

    case installation = 'installation';

    case intervention = 'intervention';
}