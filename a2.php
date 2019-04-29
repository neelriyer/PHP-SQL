<?php

// If you want to use the COMP3311 DB Access Library, include the following two lines
//
define("LIB_DIR","/import/adams/1/cs3311/public_html/19s1/assignments/a2");

//Change this before submission!!!!!!! 
require_once("/Users/niyer16/Documents/UNI/COMP3311/ass2/db.php");

//revert to:
//require_once(LIB_DIR."/db.php");

// Your DB connection parameters, e.g., database name
//
define("DB_CONNECTION","dbname=a2");

//
// Include your other common PHP code below
// E.g., common constants, functions, etc.
//

//TASK E

//function that creates grpah
function create_graph() {

	$db = dbConnect(DB_CONNECTION);

	//GET COUNT
	//sql query
	$q = "select count(distinct name) from actor";

	//query result
	$r = dbQuery($db, mkSQL($q));
	$temp = dbNext($r);
	$arr_size = $temp[0];

	////echo "arr_size = $arr_size\n";

	//CREATE ARRAY
	//sql query
	$q1 = "select name from actor order by name";

	//query result
	$r = dbQuery($db, mkSQL($q1));

	//make array
	$actors = array();
	$actors = array_fill(0, $arr_size, '');

	//read into array
	for($i = 0;$i<$arr_size;$i++) {
		$t = dbNext($r);
		$actors[$i] = $t[0];
		////echo "actor = $actors[$i]\n";
	}

	return $actors;

}

//function that lists adjacent actors
function adjacent_actors($actor) {

	$db = dbConnect(DB_CONNECTION);

	//make sql query
	$q = "

	create or replace view movies_by_actor as
	select movie.title, actor.name, movie.id

	from movie

	join acting on movie.id = acting.movie_id

	join actor on acting.actor_id = actor.id

	where upper(actor.name) = upper(%s)

	order by actor.name;


	select distinct actor.name

	from movie

	join movies_by_actor on movies_by_actor.id = movie.id

	join acting on movie.id = acting.movie_id

	join actor on acting.actor_id = actor.id 

	where upper(actor.name) != upper(%s)

	order by actor.name";

	$r = dbQuery($db, mkSQL($q, $actor, $actor));

	//no result
	if(dbNresults($r)==0) return 0;

	//number of results
	$arr_size = dbNresults($r);
	
	//make array
	$names = array();
	$names = array_fill(0, $arr_size, '');

	//read into array
	for($i = 0;$i<$arr_size;$i++) {
		$t = dbNext($r);
		$names[$i] = $t[0];
	}

	return $names;

}

//function that checks adjacency of two actors
//returns array of movies that actors have both been in. If no movies together returns 0.
function is_adjacent($actor1, $actor2) {

	$db = dbConnect(DB_CONNECTION);

	//make sql query
	$q = "

	create or replace view actor1 as
	select movie.title, actor.name, movie.id, movie.year

	from movie

	join acting on movie.id = acting.movie_id

	join actor on acting.actor_id = actor.id

	where upper(actor.name) = upper(%s)

	order by actor.name;


	create or replace view actor2 as
	select movie.title, actor.name, movie.id, movie.year

	from movie

	join acting on movie.id = acting.movie_id

	join actor on acting.actor_id = actor.id

	where upper(actor.name) = upper(%s)

	order by actor.name;


	select actor1.title, actor1.year, actor1.name as actor1, actor2.name as actor2

	from actor1

	join actor2 on actor1.id = actor2.id

";

	$r = dbQuery($db, mkSQL($q, $actor1, $actor2));

	//no result
	if(dbNresults($r)==0) return 0;

	//number of results
	$arr_size = dbNresults($r);
	
	//make array
	$movies = array();
	$movies = array_fill(0, $arr_size, '');


	//read into array
	for($i = 0;$i<$arr_size;$i++) {
		$t = dbNext($r);
		$title = $t[0];
		$year = $t[1];

		//append year and title
		$movies[$i] = $title." "."(".$year.")";
		//echo "CHECK: year is $movies[$i]\n";
	}

	return $movies;
}

//BFS
function bfs_path($graph, $start, $end) {

	//Create queue
    $queue = new SplQueue();

    //Enqueue start
    $queue->enqueue($start);

	//visited array
	$visited = array();
	for ($i = 0;$i<count($graph);$i++) $visited[$i] = -1;

	//mark as visited
    $visited[$start] = 1;

	$isFound = 0;

	$path = array();
	for ($i = 0;$i<count($graph);$i++) $path[$i] = -1;

	//while queue is not empty
	while ($queue->count() > 0 && $isFound!=1) {

		//dequeue
        $x = $queue->dequeue();

		echo "-----TESTING-------\nanalysing $graph[$x]\n";

		//If at end   	    
        if ($x == $end) {
			echo "$x and $end are the same\n"; 
			$isFound = 1;
			break;
        }

        //get adjacent names
		$adjacent_names = adjacent_actors($graph[$x]);

		//recurr for all vertices
		//adjacent to current vertex
		for ($y = 0; $y<count($graph);$y++) {

			//if not adjacent move on
			if(in_array($graph[$y], $adjacent_names)==0 || $y==$start || $y==$x) continue;
			//if(($arr = is_adjacent($graph[$y], $graph[$x]))==0 || $y==$x) continue;

			$arr = is_adjacent($graph[$y], $graph[$x]);
			echo "$graph[$y] and $graph[$x] were in:\n";
			print_r($arr);

			//if unvisited
            if ($visited[$y]==-1) {

				//mark as visited 
				$visited[$y] = 1;
	
				//add to queue
                $queue->enqueue($y);

				//add to path
				$path[$y] = $x;
            }

		}
    }

	//reverse path
	$points = 0;
	$reverse_path = array();
	for ($i = 0;$i<count($graph);$i++) $reverse_path[$i] = -1;

	if($isFound) {

		for($v = $end;$v!=$start;$v=$path[$v]) {

			$reverse_path[$points] = $v;
			$points++;

		}
	}

	//re-input reversed path
	for($j = 0; $j<$points;$j++) {
		$path[$points-($j+1)] = $reverse_path[$j];
	}

	if($points == 0) {
		return -1;
	}

	print_r($path);

	return $points;

}

//BFS
function bfs_path_v2($start, $end, $graph) {

	//Create queue
    $queue = new SplQueue();

    //Enqueue start
    $queue->enqueue($start);

    //echo "actor = $graph[$start]\n";

	//visited array
	$visited = array();
	for ($i = 0;$i<count($graph);$i++) $visited[$i] = -1;

	//mark as visited
    $visited[$start] = 1;
	
	//arrays
	$parent_names = array(array());
	$movies = array(array());

	//end not found
	$end_found = 0;

	//depth
	//actor depth for start
	$arr_depth = array();
	$arr_depth[$start] = 0;
	$depth = 1;
	$time_to_depth_increase = 1;
	$pend_depth_increase = 0;
	$max_depth = 6;

	//while queue is not empty
	while ($queue->count() > 0) {

		//dequeue
        $x = $queue->dequeue();

        //time to depth increase
        $time_to_depth_increase--;
        if($time_to_depth_increase == 0) $pend_depth_increase = 1;
        else $pend_depth_increase = 0;


        //testing
        //echo "iteration $time_to_depth_increase\n";
        //echo "time_to_depth_increase = $time_to_depth_increase\n";
        //echo "pend_depth_increase = $pend_depth_increase\n";
		//echo "\n--------TESTING-------\n $graph[$x]\n";


		//get adjacent names
		$adjacent_names = adjacent_actors($graph[$x]);
		//echo "adjacent names\n";
		//print_r($adjacent_names);


		//recurr for all vertices
		//adjacent to current vertex
		for ($y = 0; $y<count($graph);$y++) {

			//if not adjacent move on
			if(in_array($graph[$y], $adjacent_names)==0 || $y==$start) continue;
			//if(($arr = is_adjacent($graph[$y], $graph[$x]))==0 || $y==$x || $y==$start) continue;

			//echo "$graph[$y] adj to $graph[$x] in:\n";
			$arr = is_adjacent($graph[$y], $graph[$x]);
			//print_r($arr);

			//echo "\n$graph[$y] ($depth)\n";

			//if unvisited
            if ($visited[$y]==-1) {

            	//echo "$graph[$y] is unvisited\n";
			
				//mark as visited 
				$visited[$y] = 1;
					
				//add to queue
				if($depth<=$max_depth) $queue->enqueue($y);

				//if correct depth
				if($depth<=$max_depth) {

					//actor depth to depth array
					//echo "actor depth arr\n";

					//is arr depth is not initialised
					$arr_depth[$y] = $depth;
					//print_r($arr_depth);

				}

            }

			//if x is parent node
			if($arr_depth[$x] < $arr_depth[$y]) {

				$count = count($parent_names[$y]);

				//echo "count = $count\n";

				//parent node of y node is x
				$parent_names[$y][$count] = $x;

				//movies that y node has been in with parent node
				$movies[$y][$count] = $arr;

			}

			//here
			//print_r($parent_names);
			//print_r($movies);
			//echo "parent id = $x\n";
			//echo "parent name = $graph[$x]\n";
			//echo "child id = $y\n";
			//echo "child name = $graph[$y]\n";
			//echo "arr_depth of child = $arr_depth[$y]\n";
			//echo "arr_depth of parent = $arr_depth[$x]\n";
			//echo "depth = $depth\n";

            //if depth increase is pending set the timer
            if($pend_depth_increase == 1) {
				$time_to_depth_increase = $queue->count();	
			}

			 //if end found
	        if($y == $end) {

	        	//print names
	        	//echo "$graph[$y] = $y\n";
	        	//echo "parent nodes\n\n";
			    //print_r($parent_names);

			    //print depth
			    //echo "movies list\n";
			    //print_r($movies);

			    //find parent nodes, associated child nodes 
			   	//and movies that connect these two nodes
			    //recursive
			    //parent_nodes_recur($y, $parent_names, $movies, $start, $graph);

			    //end is found
				$end_found = 1;
			}
		}

		//if iterated through all children in this level of depth
		if($pend_depth_increase == 1) {

			//move to grandchildren (new depth)
			$depth++;
			//echo "depth is now $depth\n";

			//if moving to a new depth and end is already found
			if($end_found == 1) {
				//echo "\n\nreturning\n";

			   	
			   	//array of parent and children and their associated movies
				$arr_parent_children = array(array());

				//internal array count (in 2nd dimension)
				$arr_count = 0;

				//external array count(in 1st dimension)
				$global_arr_count = 0;

				//find parent nodes, associated child nodes 
			   	//and movies that connect these two nodes
			   	parent_nodes($end, $end, $parent_names, $movies, $start, $graph, $arr_parent_children, $global_arr_count, $arr_count);


				//shortest path already outputted
				return;
			}
		}	

    }

    return;
 
}

//find the parent node of a child, then find the grandparent, then g.grandparent etc.
//create array of movies that parent node has been in with child
//terminate when start node found
function parent_nodes_recur($child, $parent_names, $movies_names, $start, $graph) {

	echo "recursive function\n";

	//if at start
	if($child == $start) {
		echo "child is start\n";
		echo "child_id_recur = $child\n";
		echo "child name_recur = $graph[$child]\n";

		return;
	}

	//print_r($parent_names);
	echo "seperate\n";
	//print_r($movies_names);
	echo "child_recur = $child\n";
	//$parent_id = $parent_names[$child][0];
	echo "parent_id_recur = $parent_id\n";

	//recursive
	parent_nodes_recur($parent, $parent_names, $movies_names, $start, $graph);

}

//find parent nodes, associated child nodes and movies that connect these nodes
//create array of movies that parent node has been in with child
function parent_nodes($child, $end, $parent_names, $movies_names, $start, $graph, $arr_parent_children, $global_arr_count,  $arr_count) {

	//base case
	if($child == $start) {
		//echo "child is start\n";
		//echo "child_id_recur = $child\n";
		//echo "child name_recur = $graph[$child]\n";
		return $arr_parent_children;
	}

	//count of number of parent nodes
	$parent_node_count = count($parent_names[$child]);

	//create curr ptr and set it to child
	$curr = $child;

	//previous child
	$previous_child = "";

	//pick a parent
	for ($j = 0;$j<$parent_node_count;$j++) {	

		//echo "\n\ncount = $parent_node_count\n";
		//echo "j = $j\n";
		$parent_id = $parent_names[$curr][$j];
		$movies = $movies_names[$curr][$j];


		//print out all movies that parent and child have been in
		$movie_count = count($movies);
		foreach($movies as $names) {

			//if at last sentence of ouput, no semicolon
			if($curr == $end) {

				//if curr child the same as previous child
				if($parent_id == $previous_parent) {
					//echo "same as previous\n";
				}

				//if curr child different to previous child
				else if ($parent_id != $previous_parent && $previous_parent!="") {
					$global_arr_count++;
					$arr_count = 0;
					//echo "different to previous\n";
				}


				//echo "end of ouput\n";
				$arr_parent_children[$global_arr_count][$arr_count] = "$graph[$parent_id] was in $names with $graph[$curr]";

				//increment external and internal array count
				$arr_count++;

				$previous_parent = $parent_id;
				
				//print_r($arr_parent_children);
				
			}
			//if not at end of output, use semicolon
			else {

				//echo "previous = $previous_parent\n";

				//if curr child the same as previous child
				if($parent_id == $previous_parent) {
					//echo "same as previous\n";
				}

				//if curr child different to previous child
				else if ($parent_id != $previous_parent || $previous_parent=="") {
					$global_arr_count++;
					$arr_count = 0;
					//echo "different to previous\n";
				}

				$arr_parent_children[$global_arr_count][$arr_count] = "$graph[$parent_id] was in $names with $graph[$curr];";

				//increment internal array count
				$arr_count++;

				$previous_child = $parent_id;

				//print_r($arr_parent_children);

			}

			/*
			echo "start = $start\n";
			echo "end = $end\n";
			echo "movie names = $names\n";
			echo "child_recur = $curr\n";
			echo "child_name_recur = $graph[$curr]\n";
			echo "parent_id_recur = $parent_id\n";
			echo "parent_name_recur = $graph[$parent_id]\n";
			*/

			//if start
			if($parent_id == $start) {
				//echo "parent_id == start\n";

				$reversed = array_reverse($arr_parent_children);

				//clear array
				$arr_parent_children = array(array());

				//echo "reversed\n";
				//print_r($reversed);

				print_arr($reversed, 0);
				

				return $arr_parent_children;
			}
		}

		//echo "\n\n";

		//curr is now parent
		//update print count
		$arr_parent_children = parent_nodes($parent_id, $end, $parent_names, $movies_names, $start, $graph, $arr_parent_children, $global_arr_count, $arr_count);

	}

}

//print multi dimensional array
function print_arr($multi_arr, $internal_count) {

	//print out the first item in each array cell (compromise) cannot find a solution

	//get count of external cells
	$j=0;
	while($multi_arr[$j]) {
		$j++;
	}

	$count = $j;
	$str = "";


	//go through all external cells and gather string
	for($i = 0;$i<$count;$i++) {
		if ($multi_arr[$i][$internal_count]) $str = $str." ".$multi_arr[$i][$internal_count];
		else return;
	}

	echo "$str\n";


	print_arr($multi_arr, $internal_count+1);

}


//BFS
function bfs_search($start, $start_limit, $finish_limit, $graph) {

	//Create queue
    $queue = new SplQueue();

    //Enqueue start
    $queue->enqueue($start);

	//visited array
	$visited = array();
	for ($i = 0;$i<count($graph);$i++) $visited[$i] = -1;

	//mark as visited
    $visited[$start] = 1;

	//depth
	$depth = 1;
	$time_to_depth_increase = 1;
	$pend_depth_increase = 0;
	$max_depth = $finish_limit;
	
	//arrays
	$arr_names = array();
	$arr_depth = array();

	//while queue is not empty
	while ($queue->count() > 0) {

		//dequeue
        $x = $queue->dequeue();

        //time to depth increase
        $time_to_depth_increase--;
        if($time_to_depth_increase == 0) $pend_depth_increase = 1;
        else $pend_depth_increase = 0;

/*
        //testing
        //echo "iteration $time_to_depth_increase\n";
        //echo "time_to_depth_increase = $time_to_depth_increase\n";
        //echo "pend_depth_increase = $pend_depth_increase\n";
		//echo "\n--------TESTING-------\n $graph[$x]\n";
*/

		//get adjacent names
		$adjacent_names = adjacent_actors($graph[$x]);
		//echo "adjacent names\n";
		//print_r($adjacent_names);

		//recurr for all vertices
		//adjacent to current vertex
		for ($y = 0; $y<count($graph);$y++) {

			//if not adjacent move on
			if(in_array($graph[$y], $adjacent_names)==0 || $y==$start) continue;
			//if(($arr = is_adjacent($graph[$y], $graph[$x]))==0 || $y==$x || $y==$start) continue;

			//echo "$graph[$y] adj to $graph[$x] in:\n";
			$arr = is_adjacent($graph[$y], $graph[$x]);
			//print_r($arr);

			//echo "\n$graph[$y] ($depth)\n";

			//if unvisited
            if ($visited[$y]==-1) {
			
				//mark as visited 
				$visited[$y] = 1;

				//if correct depth
				if($depth>=$start_limit && $depth<=$finish_limit) {

					//actor name to name array
					array_push($arr_names,$graph[$y]);

					//actor depth to depth array
					array_push($arr_depth,$depth);

				}
					
				//add to queue
				if($depth<$max_depth) $queue->enqueue($y);

            }

            //if depth increase is pending set the timer
            if($pend_depth_increase == 1) {
				$time_to_depth_increase = $queue->count();	
			}
		}

		//depth increment
		if($pend_depth_increase == 1) $depth++;
		//echo "depth is now $depth\n";

    }

/*
    //testing purposes only
    //echo "arr_names print arr\n";
    $temp_arr = $arr_names;
    natcasesort($temp_arr);

    for ($j = 0;$j<count($temp_arr);$j++) {
    	$c = $j+1;
    	//echo "$c. $temp_arr[$j] ($arr_depth[$j])\n";

    }
*/

    //echo "//print_r result\n";
    //print_r($arr_names);

    //echo "arr_depth\n";
    //print_r($arr_depth);

    //split array and print by depth
    print_arr_by_depth($arr_names, $arr_depth, $start_limit);
    return;
 
}

//takes in array of actor names and array of actor name depth
//splits actor names into chunks where depth of actor name is equal
//prints actors (sorted alphabetically) in order of ascending depth
function print_arr_by_depth($arr_names, $arr_depth, $start_limit) {

	//counters
	$j = 1;
    $i = 1;
    $start = 0;
    //echo "start_limit = $start_limit\n";

    //while can find depth in array depth
    while(array_search($start_limit + $i,$arr_depth)!=FALSE) {

    	//search for depth = x (eg. depth = 1)
    	$end = array_search($start_limit + $i,$arr_depth);

    	//testing code
    	//echo "i = $i\n";
    	//echo "start = $start\n";
    	//echo "arr_search = $array_search($start_limit + $i,$arr_depth)\n";
		//echo "end  = $end\n";

		//slice array into chunk
		$temp = array_slice($arr_names,$start, $end);
		//echo "temp\n";

		//sort chunk alphabetically (case insensitive)
		natcasesort($temp);
		//uksort($temp, 'strcasecmp');

		//print_r($temp);
    	
    	//print chunk
		foreach ($temp as $val) {
			$curr_depth = $start_limit + $i - 1;
			echo "$j. $val ($curr_depth)\n";
			$j++;
		}
		
    	//increment
		$i++;

		//move start ptr to end
		$start = $end;
	}

	//last chunk
	//echo "start = $start\n";

	//slice array chunk
	$temp = array_slice($arr_names,$start);
	//echo "temp\n";

	//sort alphabetically (case insensitive)
	natcasesort($temp);
	//uksort($temp, 'strcasecmp');

	//print_r($temp);
	
	//print chunk
	foreach ($temp as $val) {
		$curr_depth = $start_limit + $i - 1;
		echo "$j. $val ($curr_depth)\n";
		$j++;
	}

}


//DFS
function print_all_paths($start, $finish, $graph) {

	$count = 0;
	$arr_size = count($graph);

	//visited array
	$visited = array();
	for ($i = 0;$i<$arr_size;$i++) $visited[$i] = -1;

	//path array
	$names = array();

	
	//call recursively
	$count = $start_limit;
	printAllPathsUtil($start, $start, $finish, $visited, $names, $graph);
	
}

//how many paths from actor1->actor2?
function printAllPathsUtil($start, $curr, $finish, $visited, $names, $graph) {

	if($curr==$finish) {
		echo "curr == finish\n";
		return;
	}

	//mark as visited
	$visited[$curr] = 1;

	echo "\n--------TESTING-------\n $graph[$curr]\n";

	//adjacent names
	$adjacent_names = adjacent_actors($graph[$curr]);

	echo "adjacent actors\n";
	print_r($adjacent_names);

	//recurr for all vertices
	//adjacent to current vertex
    for ($y = 0; $y<count($graph);$y++) {

		//if no adjacent or the same as start
		//echo "checking $graph[$y] against $graph[$curr]\n";

		if(in_array($graph[$y], $adjacent_names)==0 || $y==$start || $curr==$y) continue;
		//if(($arr = is_adjacent($graph[$y], $graph[$curr]))==0 || $start==$y || $curr==$y) continue;

		$arr = is_adjacent($graph[$y], $graph[$curr]);
		echo "$graph[$curr] and $graph[$y] were in\n";
		print_r($arr);

		//if unvisited
		if($visited[$y] == -1) {

			echo "$graph[$y] is unvisited\n";

			//print
			echo "\n$graph[$y]\n";

			//actor name to name array
			array_push($names,$graph[$y]);

			//print_r($names);

			//mark as visited
			$visited[$curr] = 1;

			//recursive
			printAllPathsUtil($start, $y, $finish, $visited, $names, $graph);

		}
    }
}


?>
