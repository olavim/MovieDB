<?php
require_once '../../include/DBFactory.php';
require_once '../../include/Response.php';

function add_entry($user_id, $data) {
    $mysqli = DBFactory::getConnection(DBFactory::CONNECTION_MAIN_DATABASE);
    $db = new MysqliDb($mysqli);
    $db->where("user_id", $user_id);
    $user_columns = $db->get("entry_columns", NULL, "column_name");

    $db->join("entry_data d2", "d1.entry_id + 1 = d2.entry_id", "LEFT");
    $db->where("d2.entry_id IS NULL");
    $entry_id = $db->getValue("entry_data d1", "MIN(d1.entry_id + 1)");

    $db->startTransaction();
    foreach ($user_columns as $column) {
        $column = $column['column_name'];
        $value = isset($data[$column]) ? $data[$column] : "";

        $d = array(
            'entry_id' => $entry_id,
            'user_id' => $user_id,
            'column_name' => $column,
            'entry_data' => $value
        );
        if (!$db->insert('entry_data', $d)) {
            $db->rollback();
            throw new Exception($db->getLastError());
        }
    }
    $db->commit();

    return $entry_id;
}

function update_entry($user_id, $entry_ids, $data) {
    $mysqli = DBFactory::getConnection(DBFactory::CONNECTION_MAIN_DATABASE);
    $db = new MysqliDb($mysqli);
    $db->where("user_id", $user_id);
    $user_columns = $db->get("entry_columns", NULL, "column_name");

    $db->where('entry_id', $entry_ids, 'IN');
    $db->where('user_id', $user_id);
    $db->get("entry_data");

    if (!$db->count) {
        Response::set('response', "No entries in the array: " . join(', ', $entry_ids));
        return false;
    }

    $num_updated = 0;

    $db->startTransaction();
    foreach ($user_columns as $column) {
        $column = $column['column_name'];
        if (isset($data[$column])) {
            $db->where('entry_id', $entry_ids, 'IN');
            $db->where('user_id', $user_id);
            $db->where('column_name', $column);

            if (!$db->update('entry_data', array('entry_data' => $data[$column]))) {
                $db->rollback();
                throw new Exception($db->getLastError());
            }

            $num_updated += $db->count;
        }
    }

    if (isset($data['picked'])) {
        $db->where('entry_id', $entry_ids, 'IN');
        $db->where('user_id', $user_id);

        $val = $data['picked'] ? true : false;
        if (!$db->update('entry_data', array('picked' => $val))) {
            $db->rollback();
            throw new Exception($db->getLastError());
        }

        $num_updated += $db->count;
    }

    $db->commit();

    return $num_updated;
}

function delete_entries($user_id, $entry_ids, $data) {
    $mysqli = DBFactory::getConnection(DBFactory::CONNECTION_MAIN_DATABASE);
    $db = new MysqliDb($mysqli);
    $db->where('entry_id', $entry_ids, 'IN');
    $db->where('user_id', $user_id);

    if (!$db->delete('entry_data')) {
        return false;
    }

    return $db->count;
}

function get_entries($id, $query) {
    $mysqli = DBFactory::getConnection(DBFactory::CONNECTION_MAIN_DATABASE);
    $db = new MysqliDb($mysqli);
    $db->where("user_id", $id);
    $user_columns = $db->get("entry_columns", NULL, "column_name");

    $sq = $db->subQuery("d2");
    $numSearch = 0;

    foreach ($user_columns as $column) {
        $column = $column['column_name'];
        if (isset($query['s_'.$column])) {
            $value = $query['s_'.$column].'*';
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
    $db->where('user_id', $id);

    if (isset($query['sort'])) {
        $sort = $mysqli->real_escape_string($query['sort']);
        $order = isset($query['order']) ? $query['order'] : 'asc';

        $db->orderBy('tt', 'asc');
        $db->orderBy('column_name', 'asc');
        $db->orderBy('entry_data', $order);

        return $db->get('entry_data d1', null, "*, (d1.column_name != '{$sort}') AS tt");
    }

    return $db->get('entry_data d1');
}

function get_entry($user_id, $entry_id) {
    $mysqli = DBFactory::getConnection(DBFactory::CONNECTION_MAIN_DATABASE);
    $db = new MysqliDb($mysqli);
    $db->where("user_id", $user_id);
    $db->where("entry_id", $entry_id);
    return $db->get("entry_data");
}

function get_array($result) {
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