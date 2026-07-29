<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Services;

use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Str;

class SiteInformationSvgStorage
{
    protected const DIRECTORY = 'svgs';

    public function __construct(
        protected FilesystemFactory $filesystem,
    ) {
    }

    public function persist(SiteInformation $siteInformation, ?string $value): void
    {
        if (! $this->isSvg($siteInformation)) {
            return;
        }

        $path = $this->path($siteInformation);

        if ($value === null || $value === '') {
            $this->filesystem->disk('s3')->delete($path);

            return;
        }

        $this->filesystem->disk('s3')->put($path, trim($value) . PHP_EOL);
    }

    public function delete(SiteInformation $siteInformation): void
    {
        if (! $this->isSvg($siteInformation)) {
            return;
        }

        $this->deletePath($this->path($siteInformation));
    }

    public function deletePath(string $path): void
    {
        $this->filesystem->disk('s3')->delete($path);
    }

    public function path(SiteInformation $siteInformation): string
    {
        return self::DIRECTORY . '/' . $this->filename($siteInformation);
    }

    public function filename(SiteInformation $siteInformation): string
    {
        $segments = explode('.', (string) $siteInformation->getAttribute('key'));
        $key = Str::slug((string) end($segments));

        return $key . '.svg';
    }

    public function isSvg(SiteInformation $siteInformation): bool
    {
        return $siteInformation->getAttribute('type') === SiteInformationFieldTypeEnum::SVG;
    }
}
