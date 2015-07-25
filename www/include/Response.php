<?php
include_once 'StatusCodes.php';

class Response
{
    private static $data;
    private static $mime_types = array(
        'application/json',
        'text/html',
        'text/plain'
    );

    public static function get_response($data, $mime = null) {
        switch ($mime) {
            case 'text/html':
                return self::html_response($data);
            case 'text/plain':
                return self::plain_response($data);
            default: // application/json
                return self::json_response($data);
        }
    }

    public static function send_response($code, $body = null) {
        $response = array();
        if (StatusCodes::isError($code)) {
            $response['status'] = 'error';
        } else {
            $response['status'] = 'success';
        }

        $response['status_code'] = $code;
        $response['status_message'] = StatusCodes::getMessageForCode($code);

        if (is_array(self::$data)) {
            foreach (self::$data as $k => $v) {
                $response[$k] = $v;
            }
        }

        $mime = self::getBestSupportedMimeType(self::$mime_types);
        header('Content-Type: ' . $mime . '; charset=utf-8');
        header(StatusCodes::httpHeaderFor($code));

        if (!StatusCodes::canHaveBody($code)) {
            $response = array();
        }

        if ($body) {
            echo self::get_response($body, $mime);
        } else {
            echo self::get_response($response, $mime);
        }

        exit;
    }

    public static function json_response($data) {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public static function html_response($data) {
        return self::arrayToString($data, true);
    }

    public static function plain_response($data) {
        return self::arrayToString($data);
    }

    public static function set($k, $v) {
        self::$data[$k] = $v;
    }

    public static function get($k) {
        return self::$data[$k];
    }

    public static function has($k) {
        return isset(self::$data[$k]);
    }

    private static function getBestSupportedMimeType($mimeTypes = null) {
        $AcceptTypes = array();

        // Accept header is case insensitive, and whitespace isn’t important
        $accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));

        // divide it into parts in the place of a ","
        $accept = explode(',', $accept);
        foreach ($accept as $a) {

            // the default quality is 1.
            $q = 1;

            // check if there is a different quality
            if (strpos($a, ';q=')) {

                // divide "mime/type;q=X" into two parts: "mime/type" & "X"
                list($a, $q) = explode(';q=', $a);
            }

            // mime-type $a is accepted with the quality $q
            // WARNING: $q == 0 means, that mime-type isn’t supported!
            $AcceptTypes[$a] = $q;
        }
        arsort($AcceptTypes);

        // if no parameter was passed, just return parsed data
        if (!$mimeTypes) return $AcceptTypes;

        $mimeTypes = array_map('strtolower', (array)$mimeTypes);

        // let’s check our supported types:
        foreach ($AcceptTypes as $mime => $q) {
            if ($q && in_array($mime, $mimeTypes)) return $mime;
        }

        // no mime-type found
        return $mimeTypes ? $mimeTypes[0] : null;
    }

    private static function arrayToString($array, $html = false)
    {
        $result = array();
        $temp = self::toTree($array, 0, $html);

        foreach($temp as $t) {
            $result[] = $t;
        }

        return implode($result);
    }

    private static function showtype($show_val)
    {
        if ($show_val === "true" || $show_val === "false") {
            return "\"{$show_val}\"";
        } else if (is_numeric($show_val)) {
            return ''.$show_val;
        } else if ($show_val === true) {
            return "true";
        } else if ($show_val === false) {
            return "false";
        } else if (is_null($show_val)) {
            return "null";
        }

        return $show_val;
    }

    private static function toTree($pieces, $depth, $html = false)
    {
        if ($html) {
            $result[] = "<ul>\n";
        }

        foreach($pieces as $k => $v) {
            $show_val = is_array($v) ? '' : $v;
            $show_val = self::showtype($show_val);

            $result[] = $html ? "<li>\n" : str_repeat("  ", $depth);

            if ($show_val == '') {
                $result[] = "{$k}:\n";
            }
            else {
                $result[] = "{$k}: " . ($html ? "<i>{$show_val}</i>" : $show_val) . "\n";
            }

            if(is_array($v)) {
                $temp = self::toTree($v, $depth+1, $html);
                $result = array_merge($result, $temp);
            }

            if ($html) {
                $result[] = "</li>\n";
            }
        }

        if ($html) {
            $result[] = "</ul>\n";
        }
        return $result;
    }
}