<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets;

enum AssetLanguage: string
{
    case Html = 'html';
    case Css = 'css';
    case Js = 'js';

    public function label(): string
    {
        return match ($this) {
            self::Html => 'HTML',
            self::Css => 'CSS',
            self::Js => 'JavaScript',
        };
    }
}
