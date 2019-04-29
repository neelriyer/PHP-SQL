# PHP-SQL
- Examining a database of actors using PHP, SQL and PLpgSQL
- completing assigned tasks

# Database
- database of actors, movies, genres, imdb votes, imdb rating, duration etc.
- please see schema file for spec

# Tasks
1. The list of movies acted by a given actor rank in ascending order (see 'acting' file)
2. List movie information by its title substring and rank by imdb votes (see 'title' file)
3. Top ranked movies by genre specified (see 'toprank' file)
4. Similar movies to one specified ranked by the number of matching keywords and genres (see 'similar' file)
5. Six degrees of Kevin Bacon (please see https://en.wikipedia.org/wiki/Six_Degrees_of_Kevin_Bacon) (see 'shortest' file)
6. Actors with N degrees of separation (same as question 5 but with a random actor, not Kevin Bacon) (see 'degrees' file)

# What I learned
- Using DFS (recursively) and BFS (with a queue) in PHP to a certain depth
- using SQL queries in PHP (using db.php which is above)
- transitioning through the returned SQL tuple in PHP
- inner joins/outer joins/left/right joins
- Creating SQL views
