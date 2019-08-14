<?php

class Eurotext_TranslationManager_Helper_Eurotext extends Mage_Core_Helper_Abstract
{
    // Like realpath, but also works on non-existing paths
    // Source: http://de2.php.net/manual/de/function.realpath.php#84012
    public function getDirectoryFromPath($path)
    {
        $rpath = $this->getAbsolutePath($path);
        $lastSep = strrpos($rpath, DIRECTORY_SEPARATOR);
        if ($lastSep >= 0) {
            return substr($rpath, 0, $lastSep);
        } else {
            return $path;
        }
    }

    // Extract directory-part of path
    // "/foo/bar/filename.dat" => "/foo/bar"
    // "/foo/bar/" => "/foo/bar"
    // "/foo/bar" => "/foo"

    public function getAbsolutePath($path)
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    // Tests if $haystack ends with $needle
    public function compareFileItems($a, $b)
    {
        return strcmp($a['full_path'], $b['full_path']);
    }

    public function extractZip($zipFile, $dstDirectory)
    {
        $dirpath = $this->removeTrailingDirectorySeparator($dstDirectory);

        $zip = new ZipArchive;
        if ($zip->open($zipFile) !== true) {
            return false;
        }

        $rv = true;

        if (!$zip->extractTo($dirpath)) {
            $rv = false;
        }

        $zip->close();

        return $rv;
    }

    public function removeTrailingDirectorySeparator($path)
    {
        return rtrim($path, DS);
    }

    public function endsWith($haystack, $needle)
    {
        if (strlen($haystack) >= strlen($needle)) {
            $lastOfHaystack = substr($haystack, -strlen($needle));

            return ($lastOfHaystack == $needle);
        }

        return false;
    }

    public function zipFolder($directory, $zipFile, $comment = "")
    {

        $helper = Mage::helper('eurotext_translationmanager');
        if (!class_exists("ZipArchive")) {
            $helper->log('ZipArchive Class does not exist!', Zend_Log::CRIT);

            return false;
        }

        $dirpath = $this->removeTrailingDirectorySeparator($directory);
        $items = $this->getDirectoryContent($dirpath, true);

        $mode = file_exists($zipFile) ? ZipArchive::OVERWRITE : ZipArchive::CREATE;

        $zip = new ZipArchive;
        $zipOpeningResult = $zip->open($zipFile, $mode);

        if ($zipOpeningResult !== true) {
            $helper->log('Could not open ZIP Archive at ' . $zipFile . '!', Zend_Log::CRIT);
            $helper->log('Reason: ' . print_r($zipOpeningResult, 1), Zend_Log::CRIT);

            return false;
        }

        if ($comment != "") {
            $zip->setArchiveComment($comment);
        }

        foreach ($items as $item) {
            if ($item['full_path'] == $zipFile) {
                // Skip
            } else {
                $inZipPath = substr($item['full_path'], strlen($dirpath) + 1);

                if ($item['type'] == "dir") {
                    $zip->addEmptyDir($inZipPath);
                } else {
                    $zip->addFile($item['full_path'], $inZipPath);
                }
            }
        }

        $zip->close();

        return true;
    }

    public function getDirectoryContent(
        $directory,
        $recurse = false,
        $enumerateFiles = true,
        $enumerateDirs = true,
        $sortResult = true
    ) {
        $result = [];

        $dirpath = $this->removeTrailingDirectorySeparator($directory);

        $dir = opendir($dirpath);
        if ($dir) {
            while (false !== ($item = readdir($dir))) {
                $full_path = $dirpath . DIRECTORY_SEPARATOR . $item;
                if (($item == ".") || ($item == "..")) {
                    // Skip
                } elseif ((is_file($full_path)) && ($enumerateFiles)) {
                    $rvItem = [];
                    $rvItem['full_path'] = $full_path;
                    $rvItem['name'] = $item;
                    $rvItem['type'] = "file";
                    array_push($result, $rvItem);
                } elseif (is_dir($full_path)) {
                    if ($enumerateDirs) {
                        $rvItem = [];
                        $rvItem['full_path'] = $full_path;
                        $rvItem['name'] = $item;
                        $rvItem['type'] = "dir";
                        array_push($result, $rvItem);
                    }

                    if ($recurse) {
                        $subresult = $this->getDirectoryContent(
                            $full_path,
                            $recurse,
                            $enumerateFiles,
                            $enumerateDirs,
                            false
                        );
                        $result = array_merge($result, $subresult);
                    }
                }
            }

            closedir($dir);
        }

        if ($sortResult) {
            usort($result, [$this, "compareFileItems"]);
        }

        return $result;
    }
}
