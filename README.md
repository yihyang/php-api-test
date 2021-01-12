# PHP Api Test

Sam has written a small e-commerce site during a hackathon

You have been hired as a developer to rewrite the application so that it can be used in production

The system needs to be able to perform the following:

1. User index endpoint
  - `/users`
  - The endpoint needs to:
    - Accept a param `?query=Product` A which allows the filter of users based on the products they purchased or the users' name
    - Accept a param `?sortBy=purchase`  which allows the sorting of users based on the purchase amount and an additional parameter `?sortOrder=asc` that allowed user to specify the sort order
    - Pagination and
2. User details endpoint
  - `/users/{id}`
  - The endpoint needs to show the user information, which include:
    - User information
    - The number of each products they purchased, sorted by descending order
    - The purchase amount they have done grouped by months
3. User leaderboard
  - `/users/ranking`
  - The endpoint need to show the list of users and their purchase amount, sorted by the purchase amount in descending order

The current relationship is as follows:
- User: The user of the system
- Product: The product that can be purchased
- Order: The order that has been created, each order only contain 1 product
