<?php

declare(strict_types=1);

namespace App\Enums;

enum VaultNodeType: string
{
    case AUDIO = 'audio';
    case FOLDER = 'folder';
    case IMAGE = 'image';
    case NOTE = 'note';
    case PDF = 'pdf';
    case VIDEO = 'video';
}
