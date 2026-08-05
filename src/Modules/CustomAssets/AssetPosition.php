<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets;

enum AssetPosition: string
{
    case Head = 'head';
    case BodyTop = 'body_top';
    case BodyBottom = 'body_bottom';

    /**
     * These read as the operator sees them in the position picker. The
     * first two were previously mislabelled — BodyTop said "Before </body>"
     * (it is the top of the body, not the end) and BodyBottom said
     * "After </body>", which is not a place anything can go. Harmless
     * while nothing rendered; actively misleading now that the host emits
     * these slots, since where a script sits changes when it runs.
     */
    public function label(): string
    {
        return match ($this) {
            self::Head => 'In <head>',
            self::BodyTop => 'Top of <body>',
            self::BodyBottom => 'Before </body>',
        };
    }
}
