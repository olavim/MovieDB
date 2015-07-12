<?php
namespace MySQLiBinder;

class MySQLiBinder
{
    private $query;
    private $types;
    private $params;
    private $stmt;

    public function __construct($mysqli, $query, $types = null)
    {
        $this->query = $query;
        $this->types = $types;
        if (!($this->stmt = $mysqli->prepare($query))) {
            throw new Exception($mysqli->error."[$query]");
        }
    }

    public function execute($params = null)
    {
        $this->params = $params;

        if ($this->types && $this->params) {
            call_user_func_array(array($this->stmt, 'bind_param'), $this->get_bind_names());
        }

        $this->stmt->execute();

        if (strtolower(explode(' ', $this->query)[0]) === 'select') {
            call_user_func_array(array($this->stmt, 'bind_result'), $this->get_parameters());

            $result_array = $this->get_results();

            return $result_array;
        } else {
            return true;
        }
    }

    public function close()
    {
        $this->stmt->close();
    }

    protected function get_bind_names()
    {
        $bind_names[] = $this->types;

        for ($i = 0; $i < count($this->params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $this->params[$i];
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
