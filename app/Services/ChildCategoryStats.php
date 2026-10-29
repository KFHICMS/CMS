<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Numbers for the admin dashboard donut chart.
 * The counting rule is documented in config/kfhi_categories.php.
 */
class ChildCategoryStats
{
    public function summary(): array
    {
        $categories = config('kfhi_categories.categories', []);

        $segments = [];
        $chartTotal = 0;

        foreach ($categories as $key => $category) {
            $count = $this->countChildren($category['keywords']);
            $chartTotal += $count;
            $segments[] = [
                'key' => $key,
                'label' => $category['label'],
                'color' => $category['color'],
                'children' => $count,
            ];
        }

        $allKeywords = collect($categories)->pluck('keywords')->flatten()->all();

        // Donut geometry: circumference is 100 units, so a segment's length equals its percentage.
        $cumulative = 0.0;
        foreach ($segments as &$segment) {
            $pct = $chartTotal > 0 ? $segment['children'] / $chartTotal * 100 : 0;
            $segment['pct'] = round($pct, 1);
            $segment['dash'] = number_format($pct, 3, '.', '');
            $segment['gap'] = number_format(100 - $pct, 3, '.', '');
            $segment['offset'] = number_format(25 - $cumulative, 3, '.', '');
            $cumulative += $pct;
        }
        unset($segment);

        return [
            'segments' => $segments,
            'chart_total' => $chartTotal,                              // sum of the four category counts
            'children_any' => $this->countChildren($allKeywords),      // each child once
            'other_records' => $this->countUnmatchedRecords($allKeywords),
        ];
    }

    /** Child ids that have a qualifying record whose category matches any keyword. */
    private function childIdQuery(array $keywords): Builder
    {
        $matches = function ($column) use ($keywords) {
            return function ($q) use ($column, $keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere($column, 'like', '%'.$keyword.'%');
                }
            };
        };

        $benefits = DB::table('field_activity_benefits as b')
            ->join('field_activity_submissions as s', 's.id', '=', 'b.submission_id')
            ->where('s.status', 'submitted')
            ->where($matches('b.category'))
            ->select('s.child_id');

        $observations = DB::table('field_activity_observations as o')
            ->join('field_activity_submissions as s', 's.id', '=', 'o.submission_id')
            ->where('s.status', 'submitted')
            ->where($matches('o.category'))
            ->select('s.child_id');

        $followUps = DB::table('follow_ups as f')
            ->where('f.status', '!=', 'cancelled')
            ->where($matches('f.category'))
            ->select('f.child_id');

        return $benefits->union($observations)->union($followUps);
    }

    private function countChildren(array $keywords): int
    {
        if ($keywords === []) {
            return 0;
        }

        return DB::query()
            ->fromSub($this->childIdQuery($keywords), 'u')
            ->join('children as c', 'c.id', '=', 'u.child_id')
            ->whereNull('c.deleted_at')
            ->distinct()
            ->count('u.child_id');
    }

    /** Qualifying records with a category text that matches none of the four categories. */
    private function countUnmatchedRecords(array $keywords): int
    {
        $notMatching = function ($column) use ($keywords) {
            return function ($q) use ($column, $keywords) {
                $q->whereNotNull($column)->where($column, '!=', '');
                foreach ($keywords as $keyword) {
                    $q->where($column, 'not like', '%'.$keyword.'%');
                }
            };
        };

        $benefits = DB::table('field_activity_benefits as b')
            ->join('field_activity_submissions as s', 's.id', '=', 'b.submission_id')
            ->where('s.status', 'submitted')->where($notMatching('b.category'))->count();

        $observations = DB::table('field_activity_observations as o')
            ->join('field_activity_submissions as s', 's.id', '=', 'o.submission_id')
            ->where('s.status', 'submitted')->where($notMatching('o.category'))->count();

        $followUps = DB::table('follow_ups as f')
            ->where('f.status', '!=', 'cancelled')->where($notMatching('f.category'))->count();

        return $benefits + $observations + $followUps;
    }
}
