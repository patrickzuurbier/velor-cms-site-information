<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationRepositoryInterface;

/**
 * @extends AbstractRepository<SiteInformation>
 */
class SiteInformationRepository extends AbstractRepository implements SiteInformationRepositoryInterface
{
    /**
     * @var class-string<SiteInformation>
     */
    protected string $model = SiteInformation::class;

    public function __construct(
        protected ConnectionInterface $database,
    ) {
    }

    /**
     * @return EloquentCollection<int, SiteInformation>
     */
    public function all(): EloquentCollection
    {
        return $this->query()->get();
    }

    /**
     * @param  array<int, string>  $ids
     * @return EloquentCollection<int, SiteInformation>
     */
    public function forIds(array $ids): EloquentCollection
    {
        return $this->query()
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForSubject(SiteInformationSubject $subject, array $attributes): SiteInformation
    {
        return $subject->siteInformation()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SiteInformation $siteInformation, array $attributes): SiteInformation
    {
        $siteInformation->fill($attributes);
        $siteInformation->save();

        return $siteInformation;
    }

    public function updateValue(SiteInformation $siteInformation, ?string $value): SiteInformation
    {
        $siteInformation->setAttribute('value', $value);
        $siteInformation->save();

        return $siteInformation;
    }

    public function delete(SiteInformation $siteInformation): void
    {
        $siteInformation->delete();
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function reorderWithinSubject(string $subjectId, array $ids): void
    {
        $this->reorder(
            ids: $ids,
            scopeColumn: 'site_information_subject_id',
            scopeValue: $subjectId,
        );
    }

    /**
     * @param  array<int, string>  $ids
     */
    protected function reorder(array $ids, string $scopeColumn, string $scopeValue): void
    {
        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return;
        }

        $this->database->transaction(function () use ($ids, $scopeColumn, $scopeValue): void {
            $items = $this->query()
                ->where($scopeColumn, $scopeValue)
                ->whereKey($ids)
                ->orderBy('sort_order')
                ->get();

            $validIds = $items->pluck('id')->map(static fn (mixed $id): string => (string) $id)->all();
            $orderedIds = array_values(array_intersect($ids, $validIds));

            foreach ($orderedIds as $index => $id) {
                $this->query()
                    ->whereKey($id)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }
}
