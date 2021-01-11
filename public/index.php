<?php
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Faker\Factory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->get('/users', function (Request $request, Response $response, array $args) {
  echo now();
    $db = new SQLite3('../data.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $statement = $db->prepare('SELECT * FROM "users"');
    $query = $statement->execute();
    $result = [];
    while ($row = $query->fetchArray()) {
        $result[] = [
          'id' => $row['id'],
        ];
    }
    $db->close();
    $response->getBody()->write(json_encode($result));
    return $response;
});

$app->get('/users/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $db = new SQLite3('../data.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $statement = $db->prepare('SELECT * FROM "users" WHERE ID = ?');
    $statement->bindValue(1, $id);
    $query = $statement->execute();
    $row = $query->fetchArray();
    $db->close();
    $response->getBody()->write(json_encode($row));
    return $response;
});

// NOTE: this is for testing purpose only
$app->get('/setup-databases', function (Request $request, Response $response, array $args) {
    $db = new SQLite3('../data.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $faker = Faker\Factory::create();
    /***************
    /* setup users *
     ***************/
    $db->query('CREATE TABLE IF NOT EXISTS "users" (
        "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        "name" VARCHAR,
        "created_at" DATETIME,
        "updated_at" DATETIME
    )');
    for ($i=0; $i < 100; $i++) {
      $statement = $db->prepare('INSERT INTO "users" ("name", "created_at", "updated_at")
      VALUES (?, ?, ?)');
      $randomDate = Carbon::now()->sub('days', rand(0, 90))->sub('minutes', rand(0, 86400));
      $statement->bindValue(1, $faker->name);
      $statement->bindValue(2, $randomDate);
      $statement->bindValue(3, $randomDate);
      $statement->execute();
    }

    /******************
     * Setup products *
     ******************/
    $db->query('CREATE TABLE IF NOT EXISTS "products" (
        "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        "name" VARCHAR,
        "sku" VARCHAR,
        "price" NUMBER,
        "created_at" DATETIME,
        "updated_at" DATETIME
    )');
    for ($i=0; $i < 100; $i++) {
      $statement = $db->prepare('INSERT INTO "products" ("name", "sku", "price", "created_at", "updated_at")
      VALUES (?, ?, ?, ?, ?)');
      $randomDate = Carbon::now()->sub('days', rand(0, 90))->sub('minutes', rand(0, 86400));
      $statement->bindValue(1, $faker->name);
      $statement->bindValue(2, $faker->uuid);
      $statement->bindValue(3, rand(1, 1000));
      $statement->bindValue(4, $randomDate);
      $statement->bindValue(5, $randomDate);
      $statement->execute();
    }

    /****************
     * Setup orders *
     ****************/
    $db->query('CREATE TABLE IF NOT EXISTS "orders" (
        "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        "user_id" VARCHAR,
        "product_id" VARCHAR,
        "quantity" INTEGER,
        "created_at" DATETIME,
        "updated_at" DATETIME
    )');
    for ($i=0; $i < 100; $i++) {
      $statement = $db->prepare('INSERT INTO orders ("product_id", "user_id", "quantity", "created_at", "updated_at")
        SELECT *, ?, ?, ? FROM
        (
        SELECT  id  FROM products ORDER BY RANDOM() LIMIT 1
        ) JOIN
        (
        SELECT  id  FROM users ORDER BY RANDOM() LIMIT 1
        )
        ON 1 = 1
      ');
      $randomDate = Carbon::now()->sub('days', rand(0, 90))->sub('minutes', rand(0, 86400));
      $statement->bindValue(1, rand(1, 10));
      $statement->bindValue(2, $randomDate);
      $statement->bindValue(3, $randomDate);
      $statement->execute();
    }

    $db->close();

    $response->getBody()->write(json_encode(true));
    return $response;
});

$app->run();
