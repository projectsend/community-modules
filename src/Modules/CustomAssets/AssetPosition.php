<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets;

enum AssetPosition: string
{
    case Head = 'head';
    case BodyTop = 'body_top';
    case BodyBottom = 'body_bottom';

    public function label(): string
    {
        return match ($this) {
            self::Head => 'In <head>',
            self::BodyTop => 'Before </body>',
            self::BodyBottom => 'After </body>',
        };
    }
}
