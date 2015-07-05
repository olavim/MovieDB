<?php
namespace MySQLiBinder;

class MySQLiBinder
{
    private $query;
    private $types;
    private $params;

    public function __construct($query, $types = null, $params = null)
    {
        $this->query = $query;
        $this->types = $types;
        $this->params = $params;
    }

    public function execute($mysqli)
    {
        if ($stmt = $mysqli->prepare($this->query)) {
            if ($this->types && $this->params) {
                call_user_func_array(array($stmt, 'bind_param'), $this->get_bind_names());
            }

            $stmt->execute();

            call_user_func_array(array($stmt, 'bind_result'), $this->get_parameters($stmt));

            $result_array = $this->get_results($stmt);

            $stmt->close();

            return $result_array;
        } else {
            print_r($mysqli->error);
        }
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

    protected function get_parameters($stmt)
    {
        $meta = $stmt->result_metadata();
        while ($field = $meta->fetch_field()) {
            $var = $field->name;
            $$var = null;
            $parameters[$field->name] = &$$var;
        }

        return $parameters;
    }

    protected function get_results($stmt)
    {
        $result = $stmt->get_result();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $result_array[] = $row;
        }
        return $result_array;
    }
}