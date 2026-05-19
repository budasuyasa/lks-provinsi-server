<?php

/**
 * Marking Criteria — SERVER MODULE PHASE 1 (Backend REST API)
 *
 * Versi 2: Section 7 (Code Quality) dihapus. Sections 1-6 di-rescale ke total 30.00.
 * Urutan dan teks 1:1 dengan SERVER_PHASE_1_MARKING_CRITERIA_v2.docx.
 *
 * Format per item: ['id' => '1.1', 'text' => '...', 'points' => 0.577, 'partial' => '...']
 */

return [
    'sections' => [
        '1' => [
            'name' => 'Authentication',
            'max' => 5.19,
            'criteria' => [
                ['id' => '1.1',  'text' => 'POST /api/auth/register creates user account and returns 201',                                  'points' => 0.577, 'partial' => 'Half credit if endpoint exists but returns wrong status'],
                ['id' => '1.2',  'text' => 'Register response includes user data (id, name, email, timestamps) and Sanctum token',          'points' => 0.577, 'partial' => 'Half credit if user data returned but token missing'],
                ['id' => '1.3',  'text' => 'Register validates required fields (full_name, email, password) — 422 with field errors',      'points' => 0.577, 'partial' => 'Half credit if some fields validated but error format inconsistent'],
                ['id' => '1.4',  'text' => 'Register validates email format and uniqueness',                                                'points' => 0.288, 'partial' => 'Half credit if format-only or uniqueness-only'],
                ['id' => '1.5',  'text' => 'Register validates password minimum 6 characters',                                              'points' => 0.288, 'partial' => 'No'],
                ['id' => '1.6',  'text' => 'POST /api/auth/login authenticates with email + password (returns 200 + token)',                'points' => 0.577, 'partial' => 'Half credit if login works but token missing'],
                ['id' => '1.7',  'text' => 'Login returns 401 with correct message on wrong credentials',                                   'points' => 0.288, 'partial' => 'Half credit if returns error but wrong status code'],
                ['id' => '1.8',  'text' => "POST /api/auth/logout deactivates the requesting device's token only (not all tokens)",         'points' => 0.865, 'partial' => 'Half credit if logout works but revokes ALL tokens instead of current'],
                ['id' => '1.9',  'text' => 'Logout returns 200 with success message',                                                       'points' => 0.288, 'partial' => '—'],
                ['id' => '1.10', 'text' => 'Sanctum token works as Bearer for protected endpoints',                                         'points' => 0.577, 'partial' => 'Half credit if token works in some endpoints but not all'],
                ['id' => '1.11', 'text' => "Protected endpoints reject missing/invalid token with 401 'Unauthenticated.'",                  'points' => 0.288, 'partial' => '—'],
            ],
        ],
        '2' => [
            'name' => 'Unit & Category',
            'max' => 2.88,
            'criteria' => [
                ['id' => '2.1', 'text' => 'GET /api/units returns 200 with array of units',                                'points' => 0.576, 'partial' => '—'],
                ['id' => '2.2', 'text' => 'Each unit includes: id, name, symbol, code, created_at, updated_at',            'points' => 0.576, 'partial' => 'Half credit if 3-4 of 6 fields present'],
                ['id' => '2.3', 'text' => 'GET /api/categories returns 200 with array of categories',                      'points' => 0.576, 'partial' => '—'],
                ['id' => '2.4', 'text' => 'Each category includes: id, name, icon, color, type (IN/OUT), timestamps',      'points' => 0.576, 'partial' => 'Half credit if 4-5 of 6 fields present'],
                ['id' => '2.5', 'text' => 'Both endpoints require Bearer token (401 without)',                             'points' => 0.576, 'partial' => 'Half credit if only one endpoint properly protected'],
            ],
        ],
        '3' => [
            'name' => 'Product CRUD',
            'max' => 8.08,
            'criteria' => [
                ['id' => '3.1',  'text' => 'POST /api/products creates product with name + unit_code (returns 201)',                            'points' => 0.866, 'partial' => 'Half credit if endpoint creates but returns wrong status/data'],
                ['id' => '3.2',  'text' => 'Created product is auto-assigned to authenticated user (user_id set automatically)',                'points' => 0.577, 'partial' => 'Half credit if user_id required in request instead of inferred from token'],
                ['id' => '3.3',  'text' => 'Product create validates name required and unit_code references valid unit (422 with errors)',     'points' => 0.577, 'partial' => 'Half credit if only one field validated'],
                ['id' => '3.4',  'text' => 'PUT /api/products/:id updates own product (returns 200 with updated data)',                         'points' => 0.866, 'partial' => 'Half credit if update works but returns wrong data'],
                ['id' => '3.5',  'text' => "Update returns 403 'Forbidden access' for other user's product",                                    'points' => 0.577, 'partial' => 'Half credit if returns 401 or 404 instead of 403'],
                ['id' => '3.6',  'text' => "Update returns 404 'Not found' for non-existent product",                                           'points' => 0.288, 'partial' => '—'],
                ['id' => '3.7',  'text' => 'DELETE /api/products/:id soft-deletes own product (returns 200)',                                   'points' => 0.866, 'partial' => 'Half credit if hard-deletes instead of soft-delete'],
                ['id' => '3.8',  'text' => "Delete returns 403 for other user's product, 404 for non-existent",                                 'points' => 0.577, 'partial' => 'Half credit if only one of 403/404 implemented correctly'],
                ['id' => '3.9',  'text' => "GET /api/products returns only authenticated user's products (200)",                                'points' => 0.866, 'partial' => "Half credit if returns all users' products instead of filtered"],
                ['id' => '3.10', 'text' => 'Each product in list includes current_stock field with correct calculated value',                   'points' => 0.866, 'partial' => 'Half credit if field present but value wrong; quarter credit if field missing'],
                ['id' => '3.11', 'text' => 'GET /api/products/:id returns detail of own product with current_stock',                            'points' => 0.577, 'partial' => 'Half credit if returns data but missing current_stock'],
                ['id' => '3.12', 'text' => "Detail returns 403 for other user's product, 404 if not found",                                     'points' => 0.577, 'partial' => 'Half credit if only one handled correctly'],
            ],
        ],
        '4' => [
            'name' => 'Stock Movement',
            'max' => 6.92,
            'criteria' => [
                ['id' => '4.1',  'text' => 'POST /api/stock-movements creates movement (returns 201 + data)',                  'points' => 0.865, 'partial' => 'Half credit if creates but wrong response structure'],
                ['id' => '4.2',  'text' => 'Validates product_id (must exist) — 422 if invalid',                               'points' => 0.577, 'partial' => '—'],
                ['id' => '4.3',  'text' => 'Validates category_id (must exist) — 422 if invalid',                              'points' => 0.577, 'partial' => '—'],
                ['id' => '4.4',  'text' => 'Validates quantity (integer, >= 1) — 422 if invalid',                              'points' => 0.577, 'partial' => 'Half credit if validates integer but not min 1'],
                ['id' => '4.5',  'text' => 'Validates date format Y-m-d — 422 if invalid',                                     'points' => 0.287, 'partial' => '—'],
                ['id' => '4.6',  'text' => 'Note field is optional (movement created without note works)',                     'points' => 0.288, 'partial' => '—'],
                ['id' => '4.7',  'text' => 'DELETE /api/stock-movements/:id deletes own movement (returns 200)',               'points' => 0.577, 'partial' => '—'],
                ['id' => '4.8',  'text' => "Delete returns 403 for other user's movement, 404 for non-existent",               'points' => 0.577, 'partial' => 'Half credit if only one of 403/404 implemented'],
                ['id' => '4.9',  'text' => 'GET /api/stock-movements returns paginated list (default per_page 25)',            'points' => 0.865, 'partial' => 'Half credit if pagination present but default wrong'],
                ['id' => '4.10', 'text' => 'Movements sorted by date descending',                                              'points' => 0.577, 'partial' => 'Half credit if sorted by created_at instead of date'],
                ['id' => '4.11', 'text' => 'Filter by month + year query parameters works',                                    'points' => 0.865, 'partial' => 'Half credit if filters by month OR year but not combined'],
                ['id' => '4.12', 'text' => 'Response includes nested product and category objects per movement',               'points' => 0.288, 'partial' => 'Half credit if only one of product/category nested'],
            ],
        ],
        '5' => [
            'name' => 'Stock Report',
            'max' => 3.46,
            'criteria' => [
                ['id' => '5.1', 'text' => 'GET /api/reports/summary-by-category/out returns 200 with summary array',                            'points' => 0.577, 'partial' => '—'],
                ['id' => '5.2', 'text' => "Each OUT item contains category object + total quantity summed across user's OUT movements",        'points' => 0.865, 'partial' => 'Half credit if structure correct but quantity sum wrong'],
                ['id' => '5.3', 'text' => 'OUT report supports month + year filter parameters',                                                 'points' => 0.288, 'partial' => 'Half credit if only month OR year filter works'],
                ['id' => '5.4', 'text' => 'GET /api/reports/summary-by-category/in returns 200 with summary array',                             'points' => 0.577, 'partial' => '—'],
                ['id' => '5.5', 'text' => "Each IN item contains category object + total quantity summed across user's IN movements",          'points' => 0.865, 'partial' => 'Half credit if structure correct but quantity sum wrong'],
                ['id' => '5.6', 'text' => 'IN report supports month + year filter parameters',                                                  'points' => 0.288, 'partial' => 'Half credit if only month OR year filter works'],
            ],
        ],
        '6' => [
            'name' => 'Cross-cutting Quality',
            'max' => 3.47,
            'criteria' => [
                ['id' => '6.1', 'text' => 'current_stock correctly increases on IN movements and decreases on OUT movements',                  'points' => 1.158, 'partial' => 'Half credit if works for IN only or OUT only; quarter if value drifts over time'],
                ['id' => '6.2', 'text' => 'Soft delete on products preserves deleted_at timestamp (deleted products not returned in list)',    'points' => 0.578, 'partial' => 'Half credit if soft delete works but deleted still shown in GET'],
                ['id' => '6.3', 'text' => 'Pagination response includes: current_page, last_page, per_page, from, to, total, data',            'points' => 0.578, 'partial' => 'Quarter credit per missing field (max half deducted)'],
                ['id' => '6.4', 'text' => "422 validation errors include all field-specific error messages in 'errors' object",                'points' => 0.578, 'partial' => 'Half credit if error format incorrect (e.g. flat array)'],
                ['id' => '6.5', 'text' => 'Forbidden (403) and Not Found (404) returned with correct status messages',                         'points' => 0.578, 'partial' => 'Half credit if status codes correct but messages inconsistent'],
            ],
        ],
    ],
];
