<?php
namespace REST;

require_once '../../vendor/autoload.php';
require_once __DIR__ . '/Response.php';
require_once 'RequestAbstract.php';

class Request extends RequestAbstract
{

    protected $mysqli;

    public function __construct($mysqli, $verb = 'GET', $requestBody = null)
    {
        parent::__construct($verb, $requestBody);
        $this->mysqli = $mysqli;
    }

    protected function addEntry() {
        $db = new \MysqliDb($this->mysqli);
        $db->where("user_id", $this->user_id);
        $user_columns = $db->get("entry_columns", NULL, "column_name");

        $db->where('user_id', $this->user_id);
        $entry_id = $db->getValue("entry_data", "MAX(entry_id + 1)");

        $db->startTransaction();
        foreach ($user_columns as $column) {
            $column = $column['column_name'];
            $value = isset($this->requestBody[$column]) ? $this->requestBody[$column] : "";

            $d = array(
                'entry_id' => $entry_id,
                'user_id' => $this->user_id,
                'column_name' => $column,
                'entry_data' => $value
            );

            if (!$db->insert('entry_data', $d)) {
                Response::set('response', $db->getLastError());
                return StatusCodes::HTTP_INTERNAL_SERVER_ERROR;
            }
        }
        $db->commit();

        Response::set('entryId', $entry_id);

        return StatusCodes::HTTP_OK;
    }

    protected function updateEntries(array $entry_ids) {
        $db = new \MysqliDb($this->mysqli);
        $db->where("user_id", $this->user_id);
        $user_columns = $db->get("entry_columns", NULL, "column_name");

        $db->where('entry_id', $entry_ids, 'IN');
        $db->where('user_id', $this->user_id);
        $db->get("entry_data");

        if (!$db->count) {
            Response::set('response', "No entries in the array: " . join(', ', $entry_ids));
            return StatusCodes::HTTP_NOT_FOUND;
        }

        $num_updated = 0;

        $db->startTransaction();
        foreach ($user_columns as $column) {
            $column = $column['column_name'];
            if (isset($this->requestBody[$column])) {
                $db->where('entry_id', $entry_ids, 'IN');
                $db->where('user_id', $this->user_id);
                $db->where('column_name', $column);

                if (!$db->update('entry_data', array('entry_data' => $this->requestBody[$column]))) {
                    Response::set('response', $db->getLastError());
                    return StatusCodes::HTTP_INTERNAL_SERVER_ERROR;
                }

                $num_updated += $db->count;
            }
        }

        if (isset($data['picked'])) {
            $db->where('entry_id', $entry_ids, 'IN');
            $db->where('user_id', $this->user_id);

            $val = $data['picked'] ? true : false;
            if (!$db->update('entry_data', array('picked' => $val))) {
                $db->rollback();
                Response::set('response', $db->getLastError());
                return StatusCodes::HTTP_INTERNAL_SERVER_ERROR;
            }

            $num_updated += $db->count;
        }

        $db->commit();

        if (!$num_updated) {
            Response::set('response', 'Nothing was changed');
            return StatusCodes::HTTP_OK;
        }

        return StatusCodes::HTTP_NO_CONTENT;
    }

    protected function deleteEntries(array $entry_ids) {
        $db = new \MysqliDb($this->mysqli);
        $db->where('entry_id', $entry_ids, 'IN');
        $db->where('user_id', $this->user_id);

        if (!$db->delete('entry_data')) {
            Response::set('response', $db->getLastError());
            return StatusCodes::HTTP_INTERNAL_SERVER_ERROR;
        }

        return StatusCodes::HTTP_NO_CONTENT;
    }

    protected function getEntries(array $entry_ids = null) {
        $db = new \MysqliDb($this->mysqli);
        $db->where("user_id", $this->user_id);
        $user_columns = $db->get("entry_columns", NULL, "column_name");
        $user_columns = array_map(function($e) {
            return $e['column_name'];
        }, $user_columns);

        $sq = $db->subQuery("d2");
        $numSearch = 0;

        foreach ($user_columns as $column) {
            if (isset($this->searchParams[$column])) {
                $value = $this->searchParams[$column].'*';
                $func = $numSearch ? 'orWhere' : 'where';
                $sq->$func("(column_name = ? AND MATCH(entry_data) AGAINST(? IN BOOLEAN MODE))", array($column, $value));

                $numSearch++;
            }
        }

        if ($numSearch) {
            $sq->having("COUNT(DISTINCT column_name, entry_data) = $numSearch");
        }

        $sq->groupBy('entry_id');
        $sq->get('entry_data', NULL, 'entry_id');

        $db->join($sq, 'd1.entry_id = d2.entry_id', 'INNER');
        $db->where('user_id', $this->user_id);

        if ($entry_ids) {
            $db->where('d1.entry_id', $entry_ids, 'IN');
        }

        if (isset($query['sort'])) {
            $sort = $this->mysqli->real_escape_string($query['sort']);
            $order = isset($query['order']) ? $query['order'] : 'asc';

            $db->orderBy('tt', 'asc');
            $db->orderBy('column_name', 'asc');
            $db->orderBy('entry_data', $order);

            $response = $db->get('entry_data d1', null, "*, (d1.column_name != '{$sort}') AS tt");
        } else {
            $response = $db->get('entry_data d1');
        }

        $response = $this->get_array($response);

        foreach ($response as $e => $entry) {
            foreach ($entry as $key => $field) {
                Response::append(array('entries', $e, $key), $field);
            }
        }

        return StatusCodes::HTTP_OK;
    }

    private function get_array($result) {
        $column_rows = array();
        $id_map = array();
        $i = 0;

        foreach ($result as $row) {
            $id = $row['entry_id'];
            if (!isset($id_map[$id])) {
                $id_map[$id] = $i++;
            }

            $column = $row['column_name'];
            $data = $row['entry_data'];
            $column_rows[$id_map[$id]]['id'] = $id;
            $column_rows[$id_map[$id]]['data'][$column] = $data;
            $column_rows[$id_map[$id]]['picked'] = $row['picked'];
        }

        return $column_rows;
    }
}