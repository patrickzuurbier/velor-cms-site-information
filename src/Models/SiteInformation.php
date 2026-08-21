<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Models;

use App\Concerns\Models\HasRowOrdering;
use App\Concerns\Models\UsesAudit;
use App\Contracts\Models\RowOrderableInterface;
use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kyslik\ColumnSortable\Sortable;
use Velor\SiteInformation\Database\Factories\SiteInformationFactory;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;

/**
 * @mixin \Eloquent
 */
class SiteInformation extends AbstractModel implements RowOrderableInterface
{
    /** @use HasFactory<SiteInformationFactory> */
    use HasFactory;
    use HasRowOrdering;
    use HasUuids;
    use Sortable;
    use UsesAudit;

    protected $table = 'site_information';

    /**
     * @var array<int, string>
     */
    protected array $rowOrderScopeColumns = [
        'site_information_subject_id',
    ];

    /**
     * @var array<int, string>
     */
    public array $sortable = [
        'label',
        'key',
        'type',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<int, string>
     */
    protected array $audit = [
        'site_information_subject_id',
        'label',
        'key',
        'type',
        'value',
        'sort_order',
    ];

    protected $fillable = [
        'site_information_subject_id',
        'label',
        'key',
        'type',
        'value',
        'sort_order',
    ];

    /**
     * @return BelongsTo<SiteInformationSubject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(SiteInformationSubject::class, 'site_information_subject_id');
    }

    /**
     * @return Factory<SiteInformation>
     */
    protected static function newFactory(): Factory
    {
        return SiteInformationFactory::new();
    }

    /**
     * @return array{
     *     type: class-string<SiteInformationFieldTypeEnum>,
     *     sort_order: 'integer',
     *     created_at: 'datetime',
     *     updated_at: 'datetime',
     * }
     */
    protected function casts(): array
    {
        return [
            'type'       => SiteInformationFieldTypeEnum::class,
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
