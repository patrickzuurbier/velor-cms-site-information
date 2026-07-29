<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Models;

use App\Concerns\Models\UsesAudit;
use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kyslik\ColumnSortable\Sortable;
use Velor\SiteInformation\Database\Factories\SiteInformationSubjectFactory;

/**
 * @mixin \Eloquent
 */
class SiteInformationSubject extends AbstractModel
{
    /** @use HasFactory<SiteInformationSubjectFactory> */
    use HasFactory;
    use HasUuids;
    use Sortable;
    use UsesAudit;

    /**
     * @var array<int, string>
     */
    public array $sortable = [
        'name',
        'key',
        'is_collapsed',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<int, string>
     */
    protected array $audit = [
        'parent_id',
        'name',
        'key',
        'is_collapsed',
        'sort_order',
    ];

    protected $fillable = [
        'parent_id',
        'name',
        'key',
        'is_collapsed',
        'sort_order',
    ];

    /**
     * @return BelongsTo<SiteInformationSubject, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<SiteInformationSubject, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<SiteInformation, $this>
     */
    public function siteInformation(): HasMany
    {
        return $this->hasMany(SiteInformation::class)->orderBy('sort_order');
    }

    /**
     * @return Factory<SiteInformationSubject>
     */
    protected static function newFactory(): Factory
    {
        return SiteInformationSubjectFactory::new();
    }

    /**
     * @return array{
     *     is_collapsed: 'boolean',
     *     sort_order: 'integer',
     *     created_at: 'datetime',
     *     updated_at: 'datetime',
     * }
     */
    protected function casts(): array
    {
        return [
            'is_collapsed' => 'boolean',
            'sort_order'   => 'integer',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }
}
