<?php

namespace App\Entity;

enum EtatInstallation: string
{
    case INSTALLATION= 'installation';

    case DESINSTALLATION= 'desinstallation';

    case PRET = 'pret';
}