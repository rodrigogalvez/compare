<?php

class comparator
{

    private static $void = [
        '/.git/*',
        '/.vs/*',
        '*/.gitignore',
        '*.tmp',
        '*.log',
        '*.rar',
        '*.7z',
        '*.backup_*',
        '/fact_xcbl/XCBL/*',
        '*/archivos/*',
        '*/Thumbs.db'
    ];

    private static function format($path, $filename)
    {
        return str_replace(DIRECTORY_SEPARATOR, "/", $path . DIRECTORY_SEPARATOR . $filename);
    }
    private static function isVoid($fileinfo)
    {
        if (in_array($fileinfo["filename"], ["./", "../"]))
            return true;

        $skip = false;
        foreach (self::$void as $value) {
            if (fnmatch($value, $fileinfo["fullpath"])) {
                // echo "ignored\t{$fileinfo["fullpath"]}\n";
                $skip = true;
                break;
            }
        }
        return $skip;
    }
    public static function dir($path)
    {
        $result = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $base = $iterator->getPath();
        $len = strlen($base);

        foreach ($iterator as $fileinfo) {

            if ($fileinfo->isFile()) {
                $filename = $fileinfo->getFilename();
                $type = "FILE";
            } else {
                $filename = $fileinfo->getFilename() . "/";
                $type = "FOLDER";
            }

            $path = substr($fileinfo->getPath(), $len);

            $entry = [
                "type" => $type,
                "filename" => $filename,
                "path" => $path,
                "fullpath" => self::format($path, $filename),
                "extension" => $fileinfo->getExtension(),
                "size" => $fileinfo->getSize(),
                "mtime" => $fileinfo->getMTime(),
                "ctime" => $fileinfo->getCTime(),
            ];
            if (self::isVoid($entry))
                continue;
            $result[] = $entry;
        }
        return [
            "base" => $base,
            "content" => $result
        ];
    }

    static private function getKey($e)
    {
        return $e["type"] . "\t" . $e["fullpath"];
    }
    static function compare($primary, $secondary)
    {
        $list = [];
        foreach ($primary as $p) {
            $pk = self::getKey($p);
            $list[$pk] = $p;
            $list[$pk]["status"] = "NEW"; // nuevo
        }
        foreach ($secondary as $s) {
            $sk = self::getKey($s);
            if (array_key_exists($sk, $list)) {
                $p = $list[$sk];
                if ($p["size"] != $s["size"]) {
                    $list[$sk]["status"] = "CHANGED"; // diferente
                } else {
                    $list[$sk]["status"] = "EQUAL"; // iguales
                }
            } else {
                $list[$sk] = $s;
                $list[$sk]["status"] = "WASTED"; // sobrante
            }
        }
        return $list;
    }
}

switch ($argc) {
    case 2:
        $primary = comparator::dir($argv[1]);
        foreach ($primary["content"] as $value) {
            if ($value["type"] == "FILE")
                echo "{$value["fullpath"]}\n";
        }
        break;
    case 3:
        $stamp = date("YmdHis");
        $primary = comparator::dir($argv[1]);
        $secondary = comparator::dir($argv[2]);
        foreach (comparator::compare($primary["content"], $secondary["content"]) as $key => $value) {
            switch ($value["status"]) {
                case "EQUAL":
                    break;
                case "NEW":
                    if ($value["type"] == "FILE") {
                        $source = str_replace("/", DIRECTORY_SEPARATOR, $primary["base"] . $value["fullpath"]);
                        $destination = str_replace("/", DIRECTORY_SEPARATOR, $secondary["base"] . $value["fullpath"]);
                        echo "copy \"{$source}\" \"{$destination}\"\n";
                    } else {
                        $destination = str_replace("/", DIRECTORY_SEPARATOR, $secondary["base"] . $value["fullpath"]);
                        echo "mkdir \"{$destination}\"\n";
                    }
                    break;
                case "CHANGED":
                    if ($value["type"] == "FILE") {
                        $source = str_replace("/", DIRECTORY_SEPARATOR, $primary["base"] . $value["fullpath"]);
                        $destination = str_replace("/", DIRECTORY_SEPARATOR, $secondary["base"] . $value["fullpath"]);
                        echo "ren \"{$destination}\" \"{$destination}.backup_{$stamp}\"\n";
                        echo "copy \"{$source}\" \"{$destination}\"\n";
                    }
                    break;
                case "WASTED":
                    if ($value["type"] == "FILE") {
                        $destination = str_replace("/", DIRECTORY_SEPARATOR, $secondary["base"] . $value["fullpath"]);
                        echo "del \"{$destination}\"\n";
                    } else {
                        $destination = str_replace("/", DIRECTORY_SEPARATOR, $secondary["base"] . $value["fullpath"]);
                        echo "rmdir \"{$destination}\"\n";
                    }
                    break;
            }
        }
        break;
    default:
        echo "{$argv[0]} primary [secondary]";
        break;

}

?>