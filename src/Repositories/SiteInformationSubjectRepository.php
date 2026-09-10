<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Relations\Relation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationSubjectRepositoryInterface;

/**
 * @extends AbstractRepository<SiteInformationSubject>
 */
class SiteInformationSubjectRepository extends AbstractRepository implements SiteInformationSubjectRepositoryInterface
{
    /**
     * @var class-string<SiteInformationSubject>
     */
    protected string $model = SiteInformationSubject::class;

    public function __construct(
        protected ConnectionInterface $database,
    ) {
    }

    /**
     * @return array<int, SiteInformationSubject>
     */
    public function rootSubjectsForPanels(): array
    {
        return $this->query()
            ->whereNull('parent_id')
            ->with([
                'siteInformation'          => fn (Relation $query): Relation => $this->ordered($query),
                'children'                 => fn (Relation $query): Relation => $this->ordered($query, 'name'),
                'children.siteInformation' => fn (Relation $query): Relation => $this->ordered($query),
                'children.children'        => fn (Relation $query): Relation => $this->ordered($query, 'name'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function subjectOptions(): array
    {
        return $this->query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('id', 'name')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function parentOptions(): array
    {
        return $this->query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('id', 'name')
            ->all();
    }

    public function findWithParent(string $id): ?SiteInformationSubject
    {
        return $this->query()
            ->with('parent')
            ->find($id);
    }

    public function hasChildren(SiteInformationSubject $subject): bool
    {
        return $subject->children()->exists();
    }

    public function loadForSvgDeletion(SiteInformationSubject $subject): SiteInformationSubject
    {
        return $subject->loadMissing([
            'siteInformation',
            'children.siteInformation',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SiteInformationSubject
    {
        return $this->query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SiteInformationSubject $subject, array $attributes): SiteInformationSubject
    {
        $subject->fill($attributes);
        $subject->save();

        return $subject;
    }

    public function delete(SiteInformationSubject $subject): void
    {
        $subject->delete();
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function reorderWithinParent(?string $parentId, array $ids): void
    {
        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return;
        }

        $this->database->transaction(function () use ($ids, $parentId): void {
            $items = $this->query()
                ->where('parent_id', $parentId)
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

    /**
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     * @template TResult
     *
     * @param  Relation<TRelatedModel, TDeclaringModel, TResult>  $query
     * @return Relation<TRelatedModel, TDeclaringModel, TResult>
     */
    protected function ordered(Relation $query, string $secondaryColumn = 'label'): Relation
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy($secondaryColumn);
    }
}
