# User/Post/Comment API Demo

## Setup

1. Run `composer install` to install dependencies.
2. Create and configure `.env` file with database credentials
3. Run `php artisan migrate` to set up database.
4. (optional) Run `php artisan db:seed` to seed DB with test data.
5. (optional) Run `./vendor/bin/phpunit` to run test suite.

## Routes

```
+----------+---------------------------+-------------+--------------------------------------------------+------------+
| Method   | URI                       | Name        | Action                                           | Middleware |
+----------+---------------------------+-------------+--------------------------------------------------+------------+
| GET|HEAD | /                         |             | Closure                                          | web        |
| GET|HEAD | api/posts                 | posts.index | App\Http\Controllers\API\PostController@index    | api        |
| GET|HEAD | api/posts/{post}          | posts.show  | App\Http\Controllers\API\PostController@show     | api        |
| GET|HEAD | api/posts/{post}/comments |             | App\Http\Controllers\API\PostController@comments | api        |
| GET|HEAD | api/users                 | users.index | App\Http\Controllers\API\UserController@index    | api        |
| GET|HEAD | api/users/{user}          | users.show  | App\Http\Controllers\API\UserController@show     | api        |
| GET|HEAD | api/users/{user}/posts    |             | App\Http\Controllers\API\UserController@posts    | api        |
+----------+---------------------------+-------------+--------------------------------------------------+------------+
```

## Overview

The API pulls in data from http://jsonplaceholder.typicode.com/. It provides resources for users, posts and comments. Retrieval of the data is managed by the `App\Services\JsonPlaceholder` class, which implements `App\Contracts\ApiProvider`, an interface for implementations that can retrieve users, posts and comments-per-post, allowing for alternatives to JsonPlaceholder.

User, post and comment retrieval is managed by repositories (e.g. `App\Repositories\PostRepository`) that cache the external API data (retrieved from the `ApiProvider`) into tables and returns the requested data, retrieved from the table.

Unit tests were made to ensure everything above worked and stayed working, and to plan out the code design.

Route requests are handled using basic Laravel capabilities, including controllers, resources and resource collections. Pagination is possible for all index routes.

Feature tests were made to test all the routes to ensure the API is working as intended from the outside.

### Potential Improvements

In keeping with the task goals, data is cached into separate database tables. A more practical approach would be to use Laravel cache drivers to cache this data to a single table, which could also cut down on some code. For improved performance, an in-memory store such as Redis could be used.

Additionally, the cache can be pre-warmed by setting up a scheduled task (CRON via Laravel's Scheduler) periodically so that no users have to wait on the JsonPlaceholder API whenever the cache expires. I gave the repositories a public `warmCache()` method which could be easily used for this.

The repositories currently do not use interfaces as there is only one implementation necessary at the current time. If much growth is needed, it would be worth considering decoupling and coding to an interface.

Currently there is no full-text searching on post titles. This could be implemented, but requires care as the feature is not standardised in SQL, so the fact that the code would become dependent on a particular relational database system is to be considered.

Some things may be a tad overengineered for demo purposes.
