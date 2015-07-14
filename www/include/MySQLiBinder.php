<?php
namespace MySQLiBinder;

class Binder
{
    protected $query_type;
    protected $table;
    protected $types;
    protected $params = array();
    protected $where_params = array();
    protected $set_params = array();
    protected $result_order;
    protected $result_order_direction;
    private $stmt;
    private $mysqli;

    public function __construct($mysqli, $table, $query_type = 'select')
    {
        $this->mysqli = $mysqli;
        $this->table = $table;
        $this->query_type = $query_type;
    }

    public function add_known_parameter($param)
    {
        $param = $this->mysqli->real_escape_string($param);
        $this->params[] = $param;
    }

    public function add_insert_parameter($param, $type = 's')
    {
        $param = $this->mysqli->real_escape_string($param);
        $this->params[] = $param;
        $this->types .= $type;
    }

    public function add_where_parameter($param, $param_type = 's', $where_type = '=')
    {
        $param = $this->mysqli->real_escape_string($param);
        $where_type = $this->mysqli->real_escape_string($where_type);
        $this->where_params[] = $param . ' ' . $where_type . ' ?';
        $this->types .= $param_type;
    }

    public function add_update_parameter($param, $param_type = 's')
    {
        if (strtolower($this->query_type) === 'update') {
            $param = $this->mysqli->real_escape_string($param);
            $this->set_params[] = $param . ' = ?';
            $this->types .= $param_type;
        } else {
            throw new \Exception("Cannot add set-parameters for query type: " . $this->query_type);
        }
    }

    public function set_result_order($order, $direction = 'asc')
    {
        if (strtolower($direction) !== 'asc' && strtolower($direction) !== 'desc') {
            throw new \Exception("Invalid order direction: value must be 'asc' or 'desc'");
        }

        if (strtolower($this->query_type) === 'select') {
            $order = $this->mysqli->real_escape_string($order);
            $this->result_order = $order;
            $this->result_order_direction = $direction;
        } else {
            throw new \Exception("Cannot set result order for query type: " . $this->query_type);
        }
    }

    public function prepare()
    {
        $query = $this->make_query();
        if (!($this->stmt = $this->mysqli->prepare($query))) {
            throw new \Exception($this->mysqli->error."[$query]");
        }
    }

    public function execute($params = null)
    {
        if ($this->types && $params) {
            call_user_func_array(array($this->stmt, 'bind_param'), $this->get_bind_names($params));
        }

        $this->stmt->execute();

        if (strtolower($this->query_type) === 'select') {
            call_user_func_array(array($this->stmt, 'bind_result'), $this->get_parameters());

            $result_array = $this->get_results();

            return $result_array;
        } else {
            return true;
        }
    }

    public function close($close_mysqli_conn = false)
    {
        $this->stmt->close();
        if ($close_mysqli_conn) {
            $this->mysqli->close();
        }
    }

    protected function make_query()
    {
        $query = $this->query_type . ' ';
        $where = $this->where_params ? "where ".join(' and ', $this->where_params) : '';

        switch (strtolower($this->query_type)) {
            case 'select':
                $order = $this->result_order ? "order by {$this->result_order} {$this->result_order_direction}" : '';
                $query .= join(',', $this->params)." from {$this->table} {$where} {$order}";
                break;
            case 'delete':
                $query .= join(',', $this->params)." from {$this->table} {$where}";
                break;
            case 'update':
                $query .= "{$this->table} set ".join(',', $this->set_params)." {$where}";
                break;
            case 'insert':
                $unknowns = join(',', array_fill(0, count($this->params), '?'));
                $query .= " into {$this->table} (".join(',', $this->params).") values ({$unknowns})";
                break;
        }

        return $query;
    }

    protected function get_bind_names($params)
    {
        $bind_names[] = $this->types;

        foreach ($params as $param) {
            $bind_name = 'bind_' . $param;
            $$bind_name = $param;
            $bind_names[] = &$$bind_name;
        }

        return $bind_names;
    }

    protected function get_parameters()
    {
        $meta = $this->stmt->result_metadata();
        if ($meta && !$this->stmt->error) {
            while ($field = $meta->fetch_field()) {
                $var = $field->name;
                $$var = null;
                $parameters[$field->name] = &$$var;
            }

            return $parameters;
        } else if ($this->stmt->error) {
            throw new \Exception($this->stmt->error);
        }
    }

    protected function get_results()
    {
        $result = $this->stmt->get_result() or trigger_error($this->stmt->error);
        if ($result && !$this->stmt->error) {
            $result_array = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $result_array[] = $row;
            }
            return $result_array;
        } else if ($this->stmt->error) {
            throw new \Exception($this->stmt->error);
        } else {
            return array();
        }
    }
}
