
--test
select movie.title, director.name, movie.year, movie.content_rating, rating.imdb_score, movie.id  

	from movie

	join acting on movie.id = acting.movie_id
	
	join actor on actor.id = acting.actor_id

	left join director on movie.director_id = director.id

	left join rating on movie.id = rating.movie_id 

	where actor.name = 'JOHN BLANCHARD'

	order by (case
            	when movie.year is null then 1 
            	else 0 
			 end), movie.year, movie.title;


--find me an actor that has been in 3 movies where there are no years recorded

select actor.name, count(movie.title)

from movie

join acting on movie.id = acting.movie_id
	
join actor on actor.id = acting.actor_id

where movie.year is null

group by actor.name

having count(movie.title)>=1;



--question 2

select movie.title, movie.year, movie.content_rating, rating.imdb_score, genre_agg.genre

	from movie

	join rating on movie.id = rating.movie_id

	join 

		(select movie_id, string_agg(genre,  ', ') as genre
		from genre
		group by movie_id
		order by movie_id) as genre_agg

		on genre_agg.movie_id = movie.id

	where title like '%war%'

--question 3
select movie.title, movie.year, movie.content_rating, movie.lang, rating.imdb_score, rating.num_voted_users, genre_agg.genre

	from movie

	join rating on movie.id = rating.movie_id

	join 

		(select movie_id, string_agg(genre,  ', ') as genre
		from genre
		group by movie_id
		order by movie_id) as genre_agg

		on genre_agg.movie_id = movie.id

	where (year between 1920 and 2019) and (year is not null)

	order by rating.imdb_score desc, rating.num_voted_users desc

	limit 20


--question 4

create or replace view genre_agg as
	select movie_id as id, string_agg(genre,  ', ') as genre
	from genre
	group by movie_id
	order by movie_id
;

create or replace view keyword_agg as
	select movie_id as id, string_agg(keyword,  ', ') as keywords
	from keyword
	group by movie_id
	order by movie_id
;

select movie.id, movie.title, genre_agg.genre, keyword_agg.keywords
from movie
join genre_agg on genre_agg.id = movie.id
join keyword_agg on keyword_agg.id = movie.id
where movie.title like '%Hairspray%';

select movie.id, movie.title, genre_agg.genre, keyword_agg.keywords
from movie
join genre_agg on genre_agg.id = movie.id
join keyword_agg on keyword_agg.id = movie.id
where movie.title like '%Happy Feet%';



--hairspray (source), happy feet (target) find keywords
create or replace view keyword_agg as
	select movie.id, movie.year, movie.title, count(source.keyword) as keyword_count
	from movie
	join keyword as source on source.movie_id = movie.id
	join

		(select movie.id, movie.title, keyword.keyword
		from movie
		join keyword on keyword.movie_id = movie.id
		where upper(movie.title) = upper('The Shawshank Redemption')) as target

		on target.keyword = source.keyword

	where upper(movie.title) != upper('The Shawshank Redemption')
	group by movie.id, movie.title
	order by count(source.keyword) desc
;



--hairspray (source), happy feet (target) find genre
create or replace view genre_agg as
	select movie.id, movie.year, movie.title, count(source.genre) as genre_count
	from movie
	join genre as source on source.movie_id = movie.id

	join

		(select movie.id, movie.title, source.genre
		from movie
		join genre as source on source.movie_id = movie.id
		where upper(movie.title) = upper('The Shawshank Redemption')) as target

		on target.genre = source.genre

	where upper(movie.title) != upper('The Shawshank Redemption')
	group by movie.id, movie.title
	order by count(source.genre) desc
;


select genre_agg.title, genre_agg.year, coalesce(genre_agg.genre_count, 0) as genre_count, coalesce(keyword_agg.keyword_count, 0) as keyword_count, rating.imdb_score as imdb_score, rating.num_voted_users as num_voted_users

from genre_agg

left join keyword_agg on keyword_agg.id = genre_agg.id

join rating on rating.movie_id = genre_agg.id

order by genre_count desc, keyword_count desc, imdb_score desc, num_voted_users desc



--question 5

--chris evans.

create or replace view actor1 as
	select movie.title, actor.name, movie.id, movie.year

	from movie

	join acting on movie.id = acting.movie_id

	join actor on acting.actor_id = actor.id

	where upper(actor.name) = upper('tom cruise')

	order by actor.name
;

create or replace view actor2 as

	select movie.title, actor.name, movie.id, movie.year

	from movie

	join acting on movie.id = acting.movie_id

	join actor on acting.actor_id = actor.id

	where upper(actor.name) = upper('jeremy renner')

	order by actor.name
;

select actor1.title,actor1.year, actor1.name as actor1, actor2.name as actor2

from actor1

join actor2 on actor1.id = actor2.id



--actors that have acted with tom cruise

select distinct actor.name

from movie

join test on test.id = movie.id

join acting on movie.id = acting.movie_id

join actor on acting.actor_id = actor.id 

where upper(actor.name) != upper('Kristin Scott Thomas')

order by actor.name





