<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Services;

use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Illuminate\Database\ConnectionInterface;

class SiteInformationOrderService
{
    public function __construct(
        protected ConnectionInterface $database,
    ) {
    }

    /**
     * @param array<int, string> $subjectIds
     */
    public function reorderSubjects(?string $parentId, array $subjectIds): void
    {
        $this->reorder(
            model: SiteInformationSubject::class,
            ids: $subjectIds,
            scopeColumn: 'parent_id',
            scopeValue: $parentId,
        );
    }

    /**
     * @param array<int, string> $fieldIds
     */
    public function reorderFields(string $subjectId, array $fieldIds): void
    {
        $this->reorder(
            model: SiteInformation::class,
            ids: $fieldIds,
            scopeColumn: 'site_information_subject_id',
            scopeValue: $subjectId,
        );
    }

    /**
     * @param class-string<SiteInformation|SiteInformationSubject> $model
     * @param array<int, string> $ids
     */
    protected function reorder(string $model, array $ids, string $scopeColumn, ?string $scopeValue): void
    {
        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return;
        }

        $this->database->transaction(function () use ($model, $ids, $scopeColumn, $scopeValue): void {
            $items = $model::query()
                ->where($scopeColumn, $scopeValue)
                ->whereKey($ids)
                ->orderBy('sort_order')
                ->get();

            $validIds = $items->pluck('id')->map(static fn (mixed $id): string => (string) $id)->all();
            $orderedIds = array_values(array_intersect($ids, $validIds));

            foreach ($orderedIds as $index => $id) {
                $model::query()
                    ->whereKey($id)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }
}
