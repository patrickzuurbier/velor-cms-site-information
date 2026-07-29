<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Enums;

use App\Concerns\Enums\EnumHelper;
use App\Contracts\Enums\HasEnumHelperInterface;

enum SiteInformationFieldTypeEnum: string implements HasEnumHelperInterface
{
    use EnumHelper;

    case EMAIL = 'email';
    case SVG = 'svg';
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case URL = 'url';

    /**
     * @return array<int, SiteInformationFieldTypeEnum>
     */
    public static function enum(): array
    {
        return self::cases();
    }
}
