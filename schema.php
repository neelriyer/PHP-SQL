<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>COMP3311 19s1 - Assignment a2</title>
<link rel='stylesheet' type='text/css' href='https://www.cse.unsw.edu.au/~cs3311/19s1/course.css'></head>
<body>
<div align='center'>
<table width='100%' border='0'>
<tr valign='top'>
<td align='left' width='25%'>
  <span class='tiny'><a href="http://www.cse.unsw.edu.au/~cs3311">COMP3311 19s1</a></span>
</td>
<td align='center' width='50%'>
  <span class='heading'>Assignment a2</span><br>
  <span class='subheading'>PHP, SQL, PLpgSQL</span>
</td>
<td align='right' width='25%'>
  <span class='tiny'><a href="http://www.cse.unsw.edu.au/~cs3311">Database Systems</a></span>
</td>
</table>
</div><p style='text-align:center'><a href='index.php'>[Assignment Spec]</a>&nbsp;&nbsp;<b>[Schema]</b></p>


<p><span class='fname'>schema.sql</span><pre>-- An instance of IMDB database for COMP3311 19s1 Assignment 2
-- Note: you do not need to load this schema file to your database, as it has been
-- embedded inside the a2.db file. You only need to load the a2.db via psql and
-- the database schema will be created with the sample data populated.


-- Standard defined DOMAINs to be used as attribute types.

-- Some rating is NULL, some are Unrated and some are Not Rated. They may (or may not) mean the same.
-- We just treat them as text labels, as they are directly from the IMDB database.
CREATE DOMAIN ContentRatingType AS varchar(9) CHECK (value IN ( 'TV-14',
                                                             'Passed',
                                                             'TV-Y',
                                                             'PG-13',
                                                             'TV-MA',
                                                             'TV-G',
                                                             'TV-Y7',
                                                             'TV-PG',
                                                             'R',
                                                             'Approved',
                                                             'Not Rated',
                                                             'X',
                                                             'GP',
                                                             'G',
                                                             'M',
                                                             'PG',
                                                             'NC-17',
                                                             'Unrated' ));

-- As far as I know, all movies are after 1900.
CREATE DOMAIN YearType AS integer CHECK (value &gt; 1900);

CREATE DOMAIN NameType AS varchar(128);

CREATE DOMAIN AmountType AS bigint CHECK (value &gt;= 0);

CREATE DOMAIN PositiveInt AS integer CHECK (value &gt;= 0);

-- All possible languages I have seen from the IMDB dataset
CREATE DOMAIN LanguageType AS varchar(10) CHECK (value IN ('Danish',
                                                        'Hebrew',
                                                        'English',
                                                        'Aboriginal',
                                                        'Telugu',
                                                        'Spanish',
                                                        'Czech',
                                                        'Polish',
                                                        'Hindi',
                                                        'None',
                                                        'Tamil',
                                                        'Cantonese',
                                                        'Kannada',
                                                        'French',
                                                        'Russian',
                                                        'Italian',
                                                        'Hungarian',
                                                        'Icelandic',
                                                        'Norwegian',
                                                        'German',
                                                        'Indonesian',
                                                        'Urdu',
                                                        'Korean',
                                                        'Chinese',
                                                        'Dutch',
                                                        'Aramaic',
                                                        'Bosnian',
                                                        'Dzongkha',
                                                        'Greek',
                                                        'Thai',
                                                        'Kazakh',
                                                        'Portuguese',
                                                        'Persian',
                                                        'Vietnamese',
                                                        'Maya',
                                                        'Zulu',
                                                        'Dari',
                                                        'Mongolian',
                                                        'Swedish',
                                                        'Mandarin',
                                                        'Panjabi',
                                                        'Swahili',
                                                        'Slovenian',
                                                        'Arabic',
                                                        'Filipino',
                                                        'Japanese',
                                                        'Romanian'));

CREATE DOMAIN GenreType AS varchar(11) CHECK (value IN ( 'Thriller',
                                                      'Film-Noir',
                                                      'Western',
                                                      'Animation',
                                                      'War',
                                                      'Family',
                                                      'Adventure',
                                                      'History',
                                                      'Musical',
                                                      'Biography',
                                                      'Horror',
                                                      'Reality-TV',
                                                      'Action',
                                                      'Comedy',
                                                      'Documentary',
                                                      'Romance',
                                                      'Fantasy',
                                                      'Drama',
                                                      'Sci-Fi',
                                                      'Sport',
                                                      'Mystery',
                                                      'News',
                                                      'Crime',
                                                      'Music',
                                                      'Short',
                                                      'Game-Show'));

CREATE DOMAIN CountryType AS varchar(20) CHECK (value IN ('Cambodia',
                                                       'Libya',
                                                       'Turkey',
                                                       'Germany',
                                                       'France',
                                                       'Colombia',
                                                       'Slovenia',
                                                       'Japan',
                                                       'Cameroon',
                                                       'Russia',
                                                       'Denmark',
                                                       'New Line',
                                                       'Netherlands',
                                                       'Official site',
                                                       'Nigeria',
                                                       'Dominican Republic',
                                                       'Egypt',
                                                       'Australia',
                                                       'Bahamas',
                                                       'Georgia',
                                                       'Slovakia',
                                                       'Argentina',
                                                       'Afghanistan',
                                                       'Czech Republic',
                                                       'West Germany',
                                                       'Brazil',
                                                       'Israel',
                                                       'Chile',
                                                       'New Zealand',
                                                       'Hungary',
                                                       'USA',
                                                       'Soviet Union',
                                                       'Mexico',
                                                       'Finland',
                                                       'Taiwan',
                                                       'Thailand',
                                                       'Peru',
                                                       'Kyrgyzstan',
                                                       'Aruba',
                                                       'Iran',
                                                       'Spain',
                                                       'South Korea',
                                                       'Ireland',
                                                       'Hong Kong',
                                                       'Iceland',
                                                       'Romania',
                                                       'Canada',
                                                       'China',
                                                       'Panama',
                                                       'South Africa',
                                                       'Kenya',
                                                       'Poland',
                                                       'Italy',
                                                       'Sweden',
                                                       'Pakistan',
                                                       'Greece',
                                                       'UK',
                                                       'India',
                                                       'Philippines',
                                                       'Switzerland',
                                                       'Indonesia',
                                                       'Belgium',
                                                       'United Arab Emirates',
                                                       'Norway',
                                                       'Bulgaria'));


-- Assume all director names are distinct (i.e., same name =&gt; same person).
CREATE TABLE Director (
id integer PRIMARY KEY, -- PG: serial
name varchar(128) UNIQUE NOT NULL, 
facebook_likes PositiveInt);

-- Assume author names are distinct
CREATE TABLE Actor (
id integer PRIMARY KEY, -- PG: serial 
name varchar(128) UNIQUE NOT NULL, 
facebook_likes PositiveInt);

-- I thought title would be distinct, but I was later told that some different movies
-- might have the same title but different years (e.g., the re-make)
CREATE TABLE Movie (
id integer PRIMARY KEY,  -- PG: serial 
title varchar(256) NOT NULL, 
YEAR YearType, 
content_rating ContentRatingType, 
duration PositiveInt, 
lang LanguageType, 
country CountryType, 
gross AmountType, 
budget AmountType, 
director_id integer REFERENCES Director(id));

-- Though technically this table can be grouped with the movie table,
-- separated as table Rating has better conceptual representation (i.e., group all ratings in
-- one entity class.
CREATE TABLE Rating (
movie_id integer PRIMARY KEY REFERENCES Movie(id), 
num_critic_for_reviews PositiveInt, 
num_user_for_reviews PositiveInt, 
num_voted_users PositiveInt, 
movie_facebook_likes PositiveInt, 
cast_total_facebook_likes PositiveInt, 
imdb_score numeric(3,1));

-- A many to many relationship
CREATE TABLE Acting (
movie_id integer REFERENCES Movie(id),  
actor_id integer REFERENCES Actor(id),  
primary key (movie_id,actor_id));

CREATE TABLE Genre (
movie_id integer REFERENCES Movie(id),
genre GenreType,
primary key (movie_id,genre));

-- A list of plot keywords that represent key features of a movie
CREATE TABLE Keyword (
movie_id integer REFERENCES Movie(id),
keyword varchar(256) not null
-- primary key (movie_id,keyword)
-- For efficiency purposes, avoid primary key indexing on long text, assume (movie_id, keyword) is unique.
);


</pre>

</body>
</html>
