<?php

namespace App\Entity;

enum EtatInstallation: string
{
    case INSTALLATION= 'installation';

    case DEINSTALLATION= 'deinstallation';

    case PRET = 'pret';
}