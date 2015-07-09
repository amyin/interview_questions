<?php

abstract class Space {
  const Open = '*';
  const Black = 'x';
  const Red = 'o';
}

abstract class Outcome {
  const Neutral = "No Winner\n";
  const Black = "x has won\n";
  const Red = "o has won\n";
}


class Board {
  
  private $board; 
  const COL = 7;
  const ROW = 6;
  const M_COL = 3;
  
  function __construct() {
    for ($ii = 0; $ii <self::COL; $ii++) {
      $row = array();
      for ($jj = 0; $jj <self::ROW; $jj++) {
        $row[$jj] = Space::Open;
      }
      $this->board[$ii] = $row;
    }
  }
  
  function __toString() {
    $s = '';
    for ($jj = 0; $jj < self::ROW; $jj++) {
      for ($ii = 0; $ii < self::COL; $ii++) {
        $s .= $this->board[$ii][$jj] . ' ';
      }
      $s .= "\n";
    }
    return $s;
  }
  
  function setSpace($col, $space) {
    if ($space !== Space::Black && $space !== Space::Red) {
      throw new Exception(
        'Cannot set space to anything other than x or o'
      );
    }
    if ($col < 0 || $col >= self::COL) {
      throw new Exception(
        'Col must be between 0 and 6'
      );
    }
    for ($ii = self::ROW - 1; $ii >= 0; $ii--) {
      if ($this->board[$col][$ii] === Space::Open) {
        $this->board[$col][$ii] = $space;
        return $this->hasWon($col, $ii);
      }
    }
    throw new Exception('Col already full');
    
  }
  
  // given a new placement check if victory
  function hasWon($col, $row) {
    $space = $this->board[$col][$row];
    if ($space === Space::Open) {
      return false;
    }
    if ($col < 0 || $row < 0 || $col >= self::COL || $row >= self::ROW) {
      throw new Exception("Col or row out of bounds");
    }
    // check vertical victory
    $has_vert = $this->checkVert($col, $row, $space);
    if ($has_vert) {
      return true;
    }
    
    // check whole row for horizontal victory
    // middle must be controlled by $space for horizontal seq
    if ($this->board[self::M_COL][$row] === $space) {
      $c = 0;
      while ($c < self::COL) {
        if ($this->board[$c][$row] === $space) {
          $count++; 
          if ($count >= 4) { 
            return true;
          }
        } else {
          $count = 0;
        }
        $c++;
      }
    }
    
    /*
      My strategy was to find the left and right diaganols
      using diag_start and then starting at those spaces
      see if there were four in a row
    */
    return $this->checkDiag($col, $row, $space);
  }
  
  function checkVert($col, $row, $space) {
    if ($row <= 2) {
      $count = 0;
      for ($r = $row; $r < self::ROW; $r++) {
        if ($this->board[$col][$r] !== $space) {
          $count = 0;
        } else {
          $count++;
          if ($count >= 4) {  
            return true;
          }
        }
      }
    }
  }
  
  function checkDiag($col, $row, $space) {
    $count = 0;

    $starts = diag_start($col, $row); 
    $c = $starts['col_r']; 
    $r = $starts['row_r'];

    while ($c >= 0 && $r >= 0) {
      if ($this->board[$c][$r] === $space) {
        $count++;
        if ($count >= 4) { 
          return true;
        }
      } else {
        $count = 0;
      }
      $c--; 
      $r--;
      
    }

    $count = 0;
    $c = $starts['col_l'];
    $r = $starts['row_l'];
    while ($c < self::COL && $r >= 0) {
      if ($this->board[$c][$r] === $space) {
        $count++;
        if ($count >= 4) {
          return true;
        }
      } else {
        $count = 0;
      }
      $c++; 
      $r--;
    }
    
    
    return false;
  }
  
  function play($moves) {
    $black = true;
    foreach ($moves as $move) {
      $victory = $this->setSpace(
        $move, 
        $black ? Space::Black : Space::Red
      );
      if ($victory) {
        return $black ? Outcome::Black : Outcome::Red;
      }
      $black = !$black;
    }
    return Outcome::Neutral;
  }
  
  
}

function diag_start($col, $row) {
  $row_r = $row;
  $col_r = $col;
  $col_l = $col;
  $row_l = $row;
  if ($row === Board::ROW - 1) {
    return array(
      'col_l' => $col_l, 
      'row_l' => $row_l,
      'col_r' => $col_r, 
      'row_r' => $row_r,
    );
  }
  if ($col !== Board::COL - 1) {
    $col_r = $col + 1;
    $row_r = $row + 1;
    while ($col_r < Board::COL - 1 && $row_r < Board::ROW - 1) {
      $col_r++;
      $row_r++;
    }
  }
  if ($col !== 0) {
    $col_l = $col - 1;
    $row_l = $row + 1;
    while ($col_l > 0 && $row_l < Board::ROW - 1) {
      $col_l--;
      $row_l++;
    }
  }

  boundary($col_l, $row_l);
  boundary($col_r, $row_r);
  
  return array(
    'col_l' => $col_l, 
    'row_l' => $row_l,
    'col_r' => $col_r, 
    'row_r' => $row_r,
  );
}

function boundary($col, $row) {
  if ($col < 0 || $row < 0 || $col >= Board::COL || $row >= Board::ROW) {
    throw new Exception ("Out of bounds");
  }
}

assert(diag_start(0,0) === array('col_l' => 0, 'row_l' => 0, 'col_r' => 5, 'row_r' => 5));
assert(diag_start(4,3) === array('col_l' => 2, 'row_l' => 5, 'col_r' => 6, 'row_r' => 5));
assert(diag_start(6,5) === array('col_l' => 6, 'row_l' => 5, 'col_r' => 6, 'row_r' => 5));
assert(diag_start(0,4) === array('col_l' => 0, 'row_l' => 4, 'col_r' => 1, 'row_r' => 5));
assert(diag_start(1,5) === array('col_l' => 1, 'row_l' => 5, 'col_r' => 1, 'row_r' => 5));
assert(diag_start(1,3) === array('col_l' => 0, 'row_l' => 4, 'col_r' => 3, 'row_r' => 5));
assert(diag_start(5,0) === array('col_l' => 0, 'row_l' => 5, 'col_r' => 6, 'row_r' => 1));

function test($name, $moves, $winner = Outcome::Black) {
  $b = new Board();
  $outcome = $b->play($moves);
  assert($outcome === $winner, $name . " failed");
  printf($name . ": ");
  printf($outcome);
  printf($b);
}

$vert_test = array(1,2,1,1,1,1,3,4,3,4,3,4,3);
test("Vertical Test1", $vert_test);

$vert_test2 = array(6,6,6,5,6,5,6,5,6);
test("Vertical Test2", $vert_test2);

$horz_test = array(1,6,2,1,3,3,4);
test("Horz Test", $horz_test);

$diag_test = array(1,2,2,3,3,2,3,4,4,4,4,5);
test("L Diag", $diag_test, Outcome::Black);

$diag_test2 = array(4, 3, 3, 2, 1,2,2,3,3,2,3,4,4,4,4,5);
test("R Diag", $diag_test2, Outcome::Red);

$diag_test3 = array(4, 3, 3, 2,6,1,5,2,3,3,2,3,4,4,4,4,6,5,5,5,5,6,6,6,6);
test("R Diag2", $diag_test3, Outcome::Black);

$diag_test4 = array(1,2,2,1,1,2,1,1,3,4,4,4,3,4,3,4,6,3);
test("Diag Test4", $diag_test4, Outcome::Red);
