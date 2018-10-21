<?PHP
/*
 * Michal Bielik
 * for Dazzler, spol. s r.o.
 */

/*
 * class Code to print HTML
 */
class Code {

	private $board;

	public function Code($rows, $columns) {
		$this->board = new Board($rows, $columns);
	}

	private function get_field_class($row, $column) {
		return ($this->board->get_field($row, $column)->get_value() ? ($this->board->get_field($row, $column)->get_group() != $this->board->get_max_pool_size() ? ' class="water"' : ' class="highlight"') : null);
	}

	private function get_field_group($row, $column) {
		return $this->board->get_field($row, $column)->get_group() ? '<B>'.$this->board->get_field($row, $column)->get_group().'</B>' : null;
	}

	public function draw() {
		$output = array('<TABLE>'."\n");
		for($i = 0; $i < $this->board->get_rows(); $i++) {
			array_push($output, '<TR>');
			for($j = 0; $j < $this->board->get_columns(); $j++) {
				/* vo vnutornom cykle sa urci, ako budu jednotlive polia vykreslene podla prislusnosti ku kaluzi */
				array_push($output, '<TD'.$this->get_field_class($i, $j).'>'.$this->get_field_group($i, $j).'</TD>');
			}
			array_push($output, '</TR>'."\n");
		}
		array_push($output, '</TABLE>'."\n");
		array_push($output, '<P>Number of pools: <B>'.$this->board->get_number_of_pools().'</B><BR/>The largest pool consists of <B>'.$this->board->get_max_pool_id().'</B> fields being marked with number <B>'.$this->board->get_max_pool_size().'</B>.</P>'."\n");
		return implode('', $output);
	}
}

/*
 * class Square defines a single field of the board
 */
class Square {

	private $value;
	private $group;
	private $pool = array();

	public function Square($value) {
		$this->value = $value;
	}

	/*
	 * adding neighbour fields with spilt water to the dependency list
	 */
	public function add_nb($x, $y) {
		if(!in_array(array($x, $y), $this->pool)) {
			array_push($this->pool, array($x, $y));
		}
	}

	/*
	 * adding non-neighbour fields with spilt water to the dependency list
	 */
	public function add_to_pool($x) {
		foreach($x as $y) {
			if(!in_array($y, $this->pool)) {
				array_push($this->pool, $y);
			}
		}
	}

	public function get_pool() {
		return $this->pool;
	}

	public function get_value() {
		return $this->value;
	}

	public function delete() {
		$this->value = false;
	}

	public function set_group($x)  {
		$this->group = $x;
	}

	public function get_group() {
		return $this->group;
	}

	public function get_pool_size() {
		return count($this->get_pool());
	}
}

/*
 * class Board defines the board composed of fields
 */
class Board {

	private $columns;
	private $rows;
	private $field;
	private $number_of_pools;
	private $max_pool_id;
	private $max_pool_size;

	/*
     * constructor that initializes the board and randomly generates fields
     */
	public function Board($rows, $columns) {
		$this->rows = $rows;
		$this->columns = $columns;
		for($i = 0; $i < $this->get_rows(); $i++) {
			for($j = 0; $j < $this->get_columns(); $j++) {
				/* the probability of spilt water shall be 20% */
				$this->field[$i][$j] = new Square(mt_rand(0,4) ? false : true);
			}
		}
		$this->check_neighboors();
		$this->create_pools();
		$this->set_pool_ids();
		$this->set_max_pool();
		$this->delete_solitudes();
	}

	public function get_columns() {
		return $this->columns;
	}

	public function get_rows() {
		return $this->rows;
	}

	public function get_field($x, $y) {
		return $this->field[$x][$y];
	}

	public function get_number_of_pools() {
		return $this->number_of_pools;
	}

	public function get_max_pool_id() {
		return $this->max_pool_id;
	}

	public function get_max_pool_size() {
		return $this->max_pool_size;
	}

	/*
     * method check_neighboors() checks all 8 neighbours of examined field and if this field also contains water, is added to dependency list
	 * in case of margin fields only existing neighbours are checked
     */
	private function check_neighboors() {
		for($i = 0; $i < $this->get_rows(); $i++) {
			for($j = 0; $j < $this->get_rows(); $j++) {
				if($this->field[$i][$j]->get_value()) {
					if($i > 0 && $j > 0 && $this->field[$i-1][$j-1]->get_value()) {
						$this->field[$i][$j]->add_nb($i-1, $j-1);
					}
					if($i > 0 && $this->field[$i-1][$j]->get_value()) {
						$this->field[$i][$j]->add_nb($i-1, $j);
					}
					if($i > 0 && $j<$this->get_columns()-1 && $this->field[$i-1][$j+1]->get_value()) {
						$this->field[$i][$j]->add_nb($i-1, $j+1);
					}
					if($j > 0 && $this->field[$i][$j-1]->get_value()) {
						$this->field[$i][$j]->add_nb($i, $j-1);
					}
					if($j < $this->get_columns()-1 && $this->field[$i][$j+1]->get_value()) {
						$this->field[$i][$j]->add_nb($i, $j+1);
					}
					if($i < $this->get_rows()-1 && $j>0 && $this->field[$i+1][$j-1]->get_value()) {
						$this->field[$i][$j]->add_nb($i+1, $j-1);
					}
					if($i < $this->get_rows()-1 && $this->field[$i+1][$j]->get_value()) {
						$this->field[$i][$j]->add_nb($i+1, $j);
					}
					if($i < $this->get_rows()-1 && $j < $this->get_columns()-1 && $this->field[$i+1][$j+1]->get_value()) {
						$this->field[$i][$j]->add_nb($i+1, $j+1);
					}
				}
			}
		}
	}

	/*
     * method create_pools() searches neighbours of neighbours - fields that are not immediate neighbours
     */
	private function create_pools() {
		for($i = 0; $i < $this->get_rows(); $i++) {
			for($j = 0; $j < $this->get_columns(); $j++) {
				$pole = $this->field[$i][$j]->get_pool();
				foreach($pole as $f => $v) {
					$this->field[$v[0]][$v[1]]->add_to_pool($pole);
				}
			}
		}
	}

	/*
     * method set_max_pool() sets the number of pool that contains most fields together with their count
     */
	private function set_max_pool() {
		$max = 0;
		$pool = 0;
		for($i = 0; $i < $this->get_rows(); $i++) {
			for($j = 0; $j < $this->get_columns(); $j++) {
				if($this->field[$i][$j]->get_pool_size() > $max) {
					$max = $this->field[$i][$j]->get_pool_size();
					$pool = $this->field[$i][$j]->get_group();
				}
			}
		}
		$this->max_pool_id = $max;
		$this->max_pool_size = $pool;
	}

	/*
     * method set_pool_ids() adds an identification number for all pool members
     */
	private function set_pool_ids() {
		$pools = 1;
		for($i = 0; $i < $this->get_rows(); $i++) {
			for($j = 0; $j < $this->get_columns(); $j++) {
				if($this->field[$i][$j]->get_value() && empty($this->field[$i][$j]->get_group()) && $this->field[$i][$j]->get_pool_size()) {
					foreach($this->field[$i][$j]->get_pool() as $a => $b) {
						$this->field[$b[0]][$b[1]]->set_group($pools);
					}
					$pools += 1;
				}
			}
		}
		$this->number_of_pools = $pools - 1;
	}

	/*
	 * solitudes (fields with water without such neighbours) are deleted
	 */
	private function delete_solitudes() {
		for($i = 0; $i < $this->get_rows(); $i++) {
			for($j = 0; $j < $this->get_columns(); $j++) {
				if($this->field[$i][$j]->get_group() == null) {
					$this->field[$i][$j]->delete();
				}
			}
		}
	}
}
?>