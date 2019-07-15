<?php
return [
    [
        'GET',
        '/activities',
        [
            'uses' => 'App\\Controllers\\ActivitiesController',
            'middleware' => [
                'scope:activities:read'
            ]
        ]
    ],
    [
        'GET',
        '/activities/{activityId}',
        [
            'uses' => 'App\\Controllers\\ActivitiesController',
            'middleware' => null
        ]
    ],
    [
        'POST',
        '/activities',
        [
            'uses' => 'App\\Controllers\\ActivitiesController',
            'middleware' => [
                'scope:activities:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/activities/{activityId}',
        [
            'uses' => 'App\\Controllers\\ActivitiesController',
            'middleware' => [
                'scope:activities:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/activities/{activityId}',
        [
            'uses' => 'App\\Controllers\\ActivitiesController',
            'middleware' => [
                'scope:activities:delete'
            ]
        ]
    ],
    [
        'GET',
        '/activities/{activityId}/modules',
        [
            'uses' => 'App\\Controllers\\ActivitiesController',
            'middleware' => null
        ]
    ],
    [
        'PATCH',
        '/activities/{activityId:[1-9]\\d*}/mark',
        [
            'uses' => 'App\\Controllers\\ActivitiesController@mark',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'PATCH',
        '/activities/{activityId:[1-9]\\d*}/unmark',
        [
            'uses' => 'App\\Controllers\\ActivitiesController@unmark',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'POST',
        '/auth/login',
        [
            'uses' => 'App\\Controllers\\AuthController@login'
        ]
    ],
    [
        'POST',
        '/auth/login/facebook',
        [
            'uses' => 'App\\Controllers\\AuthController@facebook'
        ]
    ],
    [
        'GET',
        '/auth/public-key',
        [
            'uses' => 'App\\Controllers\\AuthController@publicKey'
        ]
    ],
    [
        'POST',
        '/auth/forgot',
        [
            'uses' => 'App\\Controllers\\AuthController@forgot'
        ]
    ],
    [
        'POST',
        '/auth/reset/{token:[0-9a-fA-F]{8}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{12}}',
        [
            'uses' => 'App\\Controllers\\AuthController@reset'
        ]
    ],
    [
        'POST',
        '/braintree/webhook',
        [
            'uses' => 'App\\Controllers\\BraintreeController@webhook'
        ]
    ],
    [
        'GET',
        '/braintree/client-token',
        [
            'uses' => 'App\\Controllers\\BraintreeController@clientToken',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'GET',
        '/categories',
        [
            'uses' => 'App\\Controllers\\CategoriesController',
            'middleware' => [
                'scope:categories:read'
            ]
        ]
    ],
    [
        'GET',
        '/categories/{categoryId}',
        [
            'uses' => 'App\\Controllers\\CategoriesController',
            'middleware' => [
                'scope:categories:read'
            ]
        ]
    ],
    [
        'POST',
        '/categories',
        [
            'uses' => 'App\\Controllers\\CategoriesController',
            'middleware' => [
                'scope:categories:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/categories/{categoryId}',
        [
            'uses' => 'App\\Controllers\\CategoriesController',
            'middleware' => [
                'scope:categories:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/categories/{categoryId}',
        [
            'uses' => 'App\\Controllers\\CategoriesController',
            'middleware' => [
                'scope:categories:delete'
            ]
        ]
    ],
    [
        'GET',
        '/categories/{categoryId}/posts',
        [
            'uses' => 'App\\Controllers\\CategoriesController',
            'middleware' => [
                'scope:posts:read'
            ]
        ]
    ],
    [
        'POST',
        '/chain',
        [
            'uses' => 'App\\Controllers\\ChainerController@chain'
        ]
    ],
    [
        'GET',
        '/chapters',
        [
            'uses' => 'App\\Controllers\\ChaptersController',
            'middleware' => [
                'scope:chapters:read'
            ]
        ]
    ],
    [
        'GET',
        '/chapters/{chapterId}',
        [
            'uses' => 'App\\Controllers\\ChaptersController',
            'middleware' => null
        ]
    ],
    [
        'POST',
        '/chapters',
        [
            'uses' => 'App\\Controllers\\ChaptersController',
            'middleware' => [
                'scope:chapters:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/chapters/{chapterId}',
        [
            'uses' => 'App\\Controllers\\ChaptersController',
            'middleware' => [
                'scope:chapters:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/chapters/{chapterId}',
        [
            'uses' => 'App\\Controllers\\ChaptersController',
            'middleware' => [
                'scope:chapters:delete'
            ]
        ]
    ],
    [
        'GET',
        '/chapters/{chapterId}/notions',
        [
            'uses' => 'App\\Controllers\\ChaptersController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/chapters/{chapterId:[1-9]\\d*}/recursive',
        [
            'uses' => 'App\\Controllers\\ChaptersController@recursive'
        ]
    ],
    [
        'GET',
        '/comments',
        [
            'uses' => 'App\\Controllers\\CommentsController',
            'middleware' => [
                'scope:comments:read'
            ]
        ]
    ],
    [
        'GET',
        '/comments/{commentId}',
        [
            'uses' => 'App\\Controllers\\CommentsController',
            'middleware' => [
                'scope:comments:read'
            ]
        ]
    ],
    [
        'POST',
        '/comments',
        [
            'uses' => 'App\\Controllers\\CommentsController',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'PATCH',
        '/comments/{commentId}',
        [
            'uses' => 'App\\Controllers\\CommentsController',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'DELETE',
        '/comments/{commentId}',
        [
            'uses' => 'App\\Controllers\\CommentsController',
            'middleware' => [
                'scope:comments:delete'
            ]
        ]
    ],
    [
        'GET',
        '/comments/latest/{page:\\d+}/{perPage:[1-9]\\d*}',
        [
            'uses' => 'App\\Controllers\\CommentsController@latest',
            'middleware' => [
                'scope:comments:read'
            ]
        ]
    ],
    [
        'GET',
        '/countries',
        [
            'uses' => 'App\\Controllers\\CountriesController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/countries/{countryId}',
        [
            'uses' => 'App\\Controllers\\CountriesController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/countries/slug/{countrySlug}',
        [
            'uses' => 'App\\Controllers\\CountriesController',
            'middleware' => null
        ]
    ],
    [
        'POST',
        '/countries',
        [
            'uses' => 'App\\Controllers\\CountriesController',
            'middleware' => [
                'scope:countries:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/countries/{countryId}',
        [
            'uses' => 'App\\Controllers\\CountriesController',
            'middleware' => [
                'scope:countries:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/countries/{countryId}',
        [
            'uses' => 'App\\Controllers\\CountriesController',
            'middleware' => [
                'scope:countries:delete'
            ]
        ]
    ],
    [
        'GET',
        '/countries/{countryId}/grades',
        [
            'uses' => 'App\\Controllers\\CountriesController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/disciplines',
        [
            'uses' => 'App\\Controllers\\DisciplinesController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/disciplines/{disciplineId}',
        [
            'uses' => 'App\\Controllers\\DisciplinesController',
            'middleware' => null
        ]
    ],
    [
        'POST',
        '/disciplines',
        [
            'uses' => 'App\\Controllers\\DisciplinesController',
            'middleware' => [
                'scope:disciplines:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/disciplines/{disciplineId}',
        [
            'uses' => 'App\\Controllers\\DisciplinesController',
            'middleware' => [
                'scope:disciplines:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/disciplines/{disciplineId}',
        [
            'uses' => 'App\\Controllers\\DisciplinesController',
            'middleware' => [
                'scope:disciplines:delete'
            ]
        ]
    ],
    [
        'GET',
        '/discounts',
        [
            'uses' => 'App\\Controllers\\DiscountsController',
            'middleware' => [
                'scope:discounts:read'
            ]
        ]
    ],
    [
        'GET',
        '/discounts/{discountId}',
        [
            'uses' => 'App\\Controllers\\DiscountsController',
            'middleware' => [
                'scope:discounts:read'
            ]
        ]
    ],
    [
        'GET',
        '/discounts/code/{discountCode}',
        [
            'uses' => 'App\\Controllers\\DiscountsController',
            'middleware' => [
                'scope:discounts:read'
            ]
        ]
    ],
    [
        'POST',
        '/discounts',
        [
            'uses' => 'App\\Controllers\\DiscountsController',
            'middleware' => [
                'scope:discounts:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/discounts/{discountId}',
        [
            'uses' => 'App\\Controllers\\DiscountsController',
            'middleware' => [
                'scope:discounts:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/discounts/{discountId}',
        [
            'uses' => 'App\\Controllers\\DiscountsController',
            'middleware' => [
                'scope:discounts:delete'
            ]
        ]
    ],
    [
        'GET',
        '/discounts/{discountId}/products',
        [
            'uses' => 'App\\Controllers\\DiscountsController',
            'middleware' => [
                'scope:discounts:read'
            ]
        ]
    ],
    [
        'PATCH',
        '/discounts/{discountId}/products',
        [
            'uses' => 'App\\Controllers\\DiscountsController',
            'middleware' => [
                'scope:discounts:write'
            ]
        ]
    ],
    [
        'GET',
        '/files/explore[/{directory:[a-zA-Z0-9\\_\\-\\/\\.]+}]',
        [
            'uses' => 'App\\Controllers\\FilesController@explore',
            'middleware' => [
                'scope:files:read'
            ]
        ]
    ],
    [
        'GET',
        '/files/{file:[a-zA-Z0-9\\_\\-\\/\\.]+}',
        [
            'uses' => 'App\\Controllers\\FilesController@info',
            'middleware' => [
                'scope:files:read'
            ]
        ]
    ],
    [
        'POST',
        '/files/{file:[a-zA-Z0-9\\_\\-\\/\\.]+}',
        [
            'uses' => 'App\\Controllers\\FilesController@upload',
            'middleware' => [
                'scope:files:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/files/{file:[a-zA-Z0-9\\_\\-\\/\\.]+}',
        [
            'uses' => 'App\\Controllers\\FilesController@rename',
            'middleware' => [
                'scope:files:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/files/{file:[a-zA-Z0-9\\_\\-\\/\\.]+}',
        [
            'uses' => 'App\\Controllers\\FilesController@remove',
            'middleware' => [
                'scope:files:delete'
            ]
        ]
    ],
    [
        'GET',
        '/grades',
        [
            'uses' => 'App\\Controllers\\GradesController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/grades/{gradeId}',
        [
            'uses' => 'App\\Controllers\\GradesController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/grades/slug/{gradeSlug}',
        [
            'uses' => 'App\\Controllers\\GradesController',
            'middleware' => null
        ]
    ],
    [
        'POST',
        '/grades',
        [
            'uses' => 'App\\Controllers\\GradesController',
            'middleware' => [
                'scope:grades:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/grades/{gradeId}',
        [
            'uses' => 'App\\Controllers\\GradesController',
            'middleware' => [
                'scope:grades:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/grades/{gradeId}',
        [
            'uses' => 'App\\Controllers\\GradesController',
            'middleware' => [
                'scope:grades:delete'
            ]
        ]
    ],
    [
        'GET',
        '/grades/{gradeId}/disciplines',
        [
            'uses' => 'App\\Controllers\\GradesController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/grades/slug/{gradeSlug:[a-z0-9\\-]+}/chapters/slug/{chapterSlug:[a-z0-9\\-]+}',
        [
            'uses' => 'App\\Controllers\\GradesController@getBySlugChapterSlug'
        ]
    ],
    [
        'GET',
        '/grades/{gradeId:[1-9]\\d*}/disciplines/{disciplineId:[1-9]\\d*}/chapters/{page:\\d+}/{perPage:[1-9]\\d*}',
        [
            'uses' => 'App\\Controllers\\GradesController@chaptersByDiscipline'
        ]
    ],
    [
        'GET',
        '/levels',
        [
            'uses' => 'App\\Controllers\\LevelsController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/mails',
        [
            function () {
                return new \Illuminate\Http\JsonResponse(self::getMails());
            }
        ]
    ],
    [
        'GET',
        '/mails/transaction-success/{transactionId}',
        [
            function ($transactionId) {
                return \App\Mails\TransactionSuccessMail::mock($transactionId);
            }
        ]
    ],
    [
        'GET',
        '/marks/activities/to-revise',
        [
            'uses' => 'App\\Controllers\\MarksController@activitiesToRevise',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'GET',
        '/modules',
        [
            'uses' => 'App\\Controllers\\ModulesController',
            'middleware' => [
                'scope:modules:read'
            ]
        ]
    ],
    [
        'GET',
        '/modules/{moduleId}',
        [
            'uses' => 'App\\Controllers\\ModulesController',
            'middleware' => null
        ]
    ],
    [
        'POST',
        '/modules',
        [
            'uses' => 'App\\Controllers\\ModulesController',
            'middleware' => [
                'scope:modules:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/modules/{moduleId}',
        [
            'uses' => 'App\\Controllers\\ModulesController',
            'middleware' => [
                'scope:modules:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/modules/{moduleId}',
        [
            'uses' => 'App\\Controllers\\ModulesController',
            'middleware' => [
                'scope:modules:delete'
            ]
        ]
    ],
    [
        'GET',
        '/modules/{moduleId}/comments',
        [
            'uses' => 'App\\Controllers\\ModulesController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/modules/{moduleId}/reports',
        [
            'uses' => 'App\\Controllers\\ModulesController',
            'middleware' => [
                'scope:reports:read'
            ]
        ]
    ],
    [
        'GET',
        '/modules/{moduleId:[1-9]\\d*}/chapter/recursive',
        [
            'uses' => 'App\\Controllers\\ModulesController@chapterRecursive'
        ]
    ],
    [
        'PATCH',
        '/modules/{moduleId:[1-9]\\d*}/mark',
        [
            'uses' => 'App\\Controllers\\ModulesController@mark',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'GET',
        '/notions',
        [
            'uses' => 'App\\Controllers\\NotionsController',
            'middleware' => [
                'scope:notions:read'
            ]
        ]
    ],
    [
        'GET',
        '/notions/{notionId}',
        [
            'uses' => 'App\\Controllers\\NotionsController',
            'middleware' => null
        ]
    ],
    [
        'POST',
        '/notions',
        [
            'uses' => 'App\\Controllers\\NotionsController',
            'middleware' => [
                'scope:notions:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/notions/{notionId}',
        [
            'uses' => 'App\\Controllers\\NotionsController',
            'middleware' => [
                'scope:notions:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/notions/{notionId}',
        [
            'uses' => 'App\\Controllers\\NotionsController',
            'middleware' => [
                'scope:notions:delete'
            ]
        ]
    ],
    [
        'GET',
        '/notions/{notionId}/activities',
        [
            'uses' => 'App\\Controllers\\NotionsController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/posts',
        [
            'uses' => 'App\\Controllers\\PostsController',
            'middleware' => null
        ]
    ],
    [
        'GET',
        '/posts/{postId}',
        [
            'uses' => 'App\\Controllers\\PostsController',
            'middleware' => [
                'scope:posts:read'
            ]
        ]
    ],
    [
        'POST',
        '/posts',
        [
            'uses' => 'App\\Controllers\\PostsController',
            'middleware' => [
                'scope:posts:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/posts/{postId}',
        [
            'uses' => 'App\\Controllers\\PostsController',
            'middleware' => [
                'scope:posts:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/posts/{postId}',
        [
            'uses' => 'App\\Controllers\\PostsController',
            'middleware' => [
                'scope:posts:delete'
            ]
        ]
    ],
    [
        'GET',
        '/posts/latest',
        [
            'uses' => 'App\\Controllers\\PostsController@latest'
        ]
    ],
    [
        'GET',
        '/products',
        [
            'uses' => 'App\\Controllers\\ProductsController',
            'middleware' => [
                'scope:products:read'
            ]
        ]
    ],
    [
        'GET',
        '/products/{productId}',
        [
            'uses' => 'App\\Controllers\\ProductsController',
            'middleware' => [
                'scope:products:read'
            ]
        ]
    ],
    [
        'GET',
        '/products/slug/{productSlug}',
        [
            'uses' => 'App\\Controllers\\ProductsController',
            'middleware' => null
        ]
    ],
    [
        'POST',
        '/products',
        [
            'uses' => 'App\\Controllers\\ProductsController',
            'middleware' => [
                'scope:products:write'
            ]
        ]
    ],
    [
        'PATCH',
        '/products/{productId}',
        [
            'uses' => 'App\\Controllers\\ProductsController',
            'middleware' => [
                'scope:products:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/products/{productId}',
        [
            'uses' => 'App\\Controllers\\ProductsController',
            'middleware' => [
                'scope:products:delete'
            ]
        ]
    ],
    [
        'GET',
        '/products/{productId}/items',
        [
            'uses' => 'App\\Controllers\\ProductsController',
            'middleware' => null
        ]
    ],
    [
        'PATCH',
        '/products/{productId}/items',
        [
            'uses' => 'App\\Controllers\\ProductsController',
            'middleware' => [
                'scope:products:write'
            ]
        ]
    ],
    [
        'GET',
        '/products/{productId:[1-9]\\d*}/recursive',
        [
            'uses' => 'App\\Controllers\\ProductsController@recursive'
        ]
    ],
    [
        'GET',
        '/products/{type:all|premium|pack|training|section|lesson}',
        [
            'uses' => 'App\\Controllers\\ProductsController@paginateByType'
        ]
    ],
    [
        'GET',
        '/products/buyable/{type:all|premium|pack|training|section|lesson}',
        [
            'uses' => 'App\\Controllers\\ProductsController@buyable'
        ]
    ],
    [
        'POST',
        '/products/cart',
        [
            'uses' => 'App\\Controllers\\ProductsController@cart'
        ]
    ],
    [
        'POST',
        '/products/buy',
        [
            'uses' => 'App\\Controllers\\ProductsController@buy',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'GET',
        '/reports',
        [
            'uses' => 'App\\Controllers\\ReportsController',
            'middleware' => [
                'scope:reports:read'
            ]
        ]
    ],
    [
        'GET',
        '/reports/{reportId}',
        [
            'uses' => 'App\\Controllers\\ReportsController',
            'middleware' => [
                'scope:reports:read'
            ]
        ]
    ],
    [
        'POST',
        '/reports',
        [
            'uses' => 'App\\Controllers\\ReportsController',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'PATCH',
        '/reports/{reportId}',
        [
            'uses' => 'App\\Controllers\\ReportsController',
            'middleware' => [
                'scope:reports:write'
            ]
        ]
    ],
    [
        'DELETE',
        '/reports/{reportId}',
        [
            'uses' => 'App\\Controllers\\ReportsController',
            'middleware' => [
                'scope:reports:delete'
            ]
        ]
    ],
    [
        'GET',
        '/scopes',
        [
            'uses' => 'App\\Controllers\\ScopesController@all'
        ]
    ],
    [
        'GET',
        '/search/{gradeId:[1-9]\\d*}/{query:[^\\/]+}',
        [
            'uses' => 'App\\Controllers\\SearchController@content'
        ]
    ],
    [
        'POST',
        '/subscriptions',
        [
            'uses' => 'App\\Controllers\\SubscriptionsController@create',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'PATCH',
        '/subscriptions/{subscriptionId:[a-z0-9\\_]+}',
        [
            'uses' => 'App\\Controllers\\SubscriptionsController@update',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'GET',
        '/subscriptions/{subscriptionId:[a-z0-9\\_]+}',
        [
            'uses' => 'App\\Controllers\\SubscriptionsController@find',
            'middleware' => [
                'scope:subscriptions:read'
            ]
        ]
    ],
    [
        'POST',
        '/subscriptions/{subscriptionId:[a-z0-9\\_]+}/cancel',
        [
            'uses' => 'App\\Controllers\\SubscriptionsController@cancel',
            'middleware' => [
                'scope:subscriptions:write'
            ]
        ]
    ],
    [
        'GET',
        '/',
        [
            'uses' => 'App\\Controllers\\SystemController@base'
        ]
    ],
    [
        'GET',
        '/system/health[/{count:[1-9]\\d*}]',
        [
            'uses' => 'App\\Controllers\\SystemController@health'
        ]
    ],
    [
        'GET',
        '/system/sitemap',
        [
            'uses' => 'App\\Controllers\\SystemController@sitemap'
        ]
    ],
    [
        'GET',
        '/transactions/by-user/{userId:[a-z0-9\\_]+}',
        [
            'uses' => 'App\\Controllers\\TransactionsController@byUser'
        ]
    ],
    [
        'GET',
        '/transactions/by-subscription/{subscriptionId:[a-z0-9\\_]+}',
        [
            'uses' => 'App\\Controllers\\TransactionsController@bySubscription',
            'middleware' => [
                'scope:transactions:read'
            ]
        ]
    ],
    [
        'GET',
        '/transactions/{transactionId:[a-z0-9\\_]+}',
        [
            'uses' => 'App\\Controllers\\TransactionsController@find',
            'middleware' => [
                'scope:transactions:read'
            ]
        ]
    ],
    [
        'POST',
        '/transactions/{transactionId:[a-z0-9\\_]+}/void',
        [
            'uses' => 'App\\Controllers\\TransactionsController@void',
            'middleware' => [
                'scope:transactions:write'
            ]
        ]
    ],
    [
        'POST',
        '/transactions/{transactionId:[a-z0-9\\_]+}/refund',
        [
            'uses' => 'App\\Controllers\\TransactionsController@refund',
            'middleware' => [
                'scope:transactions:write'
            ]
        ]
    ],
    [
        'GET',
        '/users',
        [
            'uses' => 'App\\Controllers\\UsersController',
            'middleware' => [
                'scope:users:read'
            ]
        ]
    ],
    [
        'GET',
        '/users/{userId}',
        [
            'uses' => 'App\\Controllers\\UsersController',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'POST',
        '/users',
        [
            'uses' => 'App\\Controllers\\UsersController',
            'middleware' => null
        ]
    ],
    [
        'PATCH',
        '/users/{userId}',
        [
            'uses' => 'App\\Controllers\\UsersController',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'DELETE',
        '/users/{userId}',
        [
            'uses' => 'App\\Controllers\\UsersController',
            'middleware' => [
                'scope:users:delete'
            ]
        ]
    ],
    [
        'GET',
        '/users/{userId}/records',
        [
            'uses' => 'App\\Controllers\\UsersController',
            'middleware' => [
                'scope:records:read'
            ]
        ]
    ],
    [
        'GET',
        '/users/{userId}/subscriptions',
        [
            'uses' => 'App\\Controllers\\UsersController',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'GET',
        '/users/{userId}/transactions',
        [
            'uses' => 'App\\Controllers\\UsersController',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'POST',
        '/users/{userId:[1-9]\\d*}/points',
        [
            'uses' => 'App\\Controllers\\UsersController@points',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'GET',
        '/users/search/{query}/{page:\\d+}/{perPage:[1-9]\\d*}',
        [
            'uses' => 'App\\Controllers\\UsersController@search',
            'middleware' => [
                'scope:users:read'
            ]
        ]
    ],
    [
        'DELETE',
        '/users/{userId:[1-9]\\d*}/cache',
        [
            'uses' => 'App\\Controllers\\UsersController@cache',
            'middleware' => [
                'scope:users:write'
            ]
        ]
    ],
    [
        'POST',
        '/votes/comment/{commentId:[1-9]\\d*}',
        [
            'uses' => 'App\\Controllers\\VotesController@vote',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ],
    [
        'DELETE',
        '/votes/comment/{commentId:[1-9]\\d*}',
        [
            'uses' => 'App\\Controllers\\VotesController@unVote',
            'middleware' => [
                'scope:logged'
            ]
        ]
    ]
];
