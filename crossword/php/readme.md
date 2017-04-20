# Simple crossword puzzle solver

A simple and reasonably fast crossword puzzle solver written in PHP 7.1.

To run:

`php crossword.php < ../test_cases/input_big_1.txt`

To run with in-progress display:

`php crossword.php < ../test_cases/input_big_1.txt display`

Data structures: string (tabular data), hashmap

Algorithm: backtracking

## TODO: explain data structures

```
$table = [
    ['+', '-', '+', '+', '+', '+', '+', '+', '+', '+'],
    ['+', '-', '+', '+', '+', '+', '+', '+', '+', '+'],
    ['+', '-', '+', '+', '+', '+', '+', '+', '+', '+'],
    ['+', '-', '-', '-', '-', '-', '+', '+', '+', '+'],
    ['+', '-', '+', '+', '+', '-', '+', '+', '+', '+'],
    ['+', '-', '+', '+', '+', '-', '+', '+', '+', '+'],
    ['+', '+', '+', '+', '+', '-', '+', '+', '+', '+'],
    ['+', '+', '-', '-', '-', '-', '-', '-', '+', '+'],
    ['+', '+', '+', '+', '+', '-', '+', '+', '+', '+'],
    ['+', '+', '+', '+', '+', '-', '+', '+', '+', '+'],
];

$words = [
    0 => LONDON,
    1 => DELHI,
    2 => ICELAND,
    3 => ANKARA,
];

$segments = [
    0 => 'v|0|1|5|1|6',
    1 => 'h|3|1|3|5|5',
    2 => 'v|3|5|9|5|7',
    3 => 'h|7|2|7|7|6',
];

$wordLengthsWordsMap = [
    6 => [ 0 => 'LONDON', 3 => 'ANKARA' ],
    5 => [ 1 => 'DELHI' ],
    7 => [ 2 => 'ICELAND' ],
];

$segmentsCrossesMap = [
    0 => [ 1 => [3, 1] ],
    1 => [ 0 => [3, 1], 2 => [3, 5] ],
    2 => [ 1 => [3, 5], 3 => [7, 5] ],
    3 => [ 2 => [7, 5] ],
];

$segmentWordPairs = [] -> [[0,0]] -> [[0,0], [1,1]] -> [[0,0], [1,1], [2,2]] -> [[0,0], [1,1], [2,2], [3,3]];

$solution = [[0,0], [1,1], [2,2], [3,3]];
```
