<?php
namespace sugerwatch\filter;

class CompressGz extends \sugerwatch\Filter
{

    private $m_filepattern;

    public function __construct($options)
    {
        $this->m_filepattern = @$options['file_pattern'] ?: '*';
    }

    public function changed($file)
    {
        $pattern = $this->m_filepattern;
        if (preg_match("/{$pattern}/", $file)) {
            $gzf = $file . '.gz';
            $fp = gzopen($gzf, 'wb');
            flock($fp, LOCK_EX);
            gzwrite($fp, file_get_contents($file), filesize($file));
            gzclose($fp);
        }
    }
}