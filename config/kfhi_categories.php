<?php

/*
|--------------------------------------------------------------------------
| Child categories for the admin dashboard donut chart
|--------------------------------------------------------------------------
| The database does not store a "category" for a child. The only category
| information is the free-text "category" field officers and admins type on:
|   - field activity benefits      (field_activity_benefits.category)
|   - field activity observations  (field_activity_observations.category)
|   - follow-ups                   (follow_ups.category)
|
| COUNTING RULE
|   A child is counted ONCE in a category when at least one qualifying record
|   about that child has a category text containing one of the category's
|   keywords (case-insensitive). Counting uses DISTINCT child, so ten benefits
|   for the same child still count as one child in that category.
|   A child with records in different categories is counted once in each of
|   those categories, so the four totals can add up to more than the number of
|   children. The centre of the donut shows each child only once.
|
|   Qualifying records:
|     - benefits and observations that belong to a SUBMITTED field submission
|       (drafts are ignored);
|     - follow-ups that are not cancelled.
|   Deleted (archived) children are never counted.
|
| Edit the keywords to match the wording your staff really use.
*/

return [
    'categories' => [
        'education' => [
            'label' => 'Education',
            'color' => '#1976D2',
            'keywords' => ['education', 'school', 'tuition', 'scholarship', 'uniform', 'stationery', 'book', 'study'],
        ],
        'health' => [
            'label' => 'Health',
            'color' => '#2E7D32',
            'keywords' => ['health', 'medical', 'medicine', 'clinic', 'hospital', 'vaccin', 'dental', 'disabilit'],
        ],
        'nutrition' => [
            'label' => 'Nutrition',
            'color' => '#F9A825',
            'keywords' => ['nutrition', 'food', 'meal', 'ration', 'milk', 'feeding', 'hunger'],
        ],
        'shelter' => [
            'label' => 'Shelter',
            'color' => '#546E7A',
            'keywords' => ['shelter', 'housing', 'house', 'roof', 'accommodation', 'repair'],
        ],
    ],
];
